<?php

namespace App;

use App\Exceptions\SpotifyAccountNotLinkedException;
use App\Http\Services\SpotifyService;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Relations\BelongsToMany;
use Jenssegers\Mongodb\Relations\HasMany;
use SpotifyWebAPI\SpotifyWebAPI;

/**
 * @property string spotify_access_token
 * @property string spotify_refresh_token
 * @property string messenger_sender_id
 * @property array asGuestPlaylists
 * @property string id
 * @property int spotify_token_expiration
 * @property array spotify_user_data
 */
class User extends \Jenssegers\Mongodb\Auth\User
{
    use Notifiable;

    /**
     * MongoDB collection for users
     *
     * @var string
     */
    protected $collection = 'users';

    /**
     * User collection primary key
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'messenger_sender_id',
        'spotify_access_token',
        'spotify_refresh_token',
        'spotify_token_expiration',
        'spotify_user_data',
        'playlist_as_guest_ids'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function isLinkedToSpotify(): bool
    {
        return isset($this->spotify_access_token) && !empty($this->spotify_access_token);
    }

    public function playlists(): HasMany
    {
        return $this->HasMany('App\Playlist');
    }


    public function merge(User &$secondUser)
    {

        // mege attributes
        foreach ($this->fillable as $field) {
            if ($this->getAttribute($field) === null
                && $secondUser->getAttribute($field) !== $this->getAttribute($field)) {
                $this->setAttribute($field, $secondUser->getAttribute($field));
            }
        }

        // merge playlists
        $secondUser->playlists()->update(['created_by_user_id' => $secondUser]);

        // merge playlists as guests
        foreach ($secondUser->playlist_as_guest_ids as $playlist) {
            $secondUser->playlistAsGuests()->dissociate($playlist);
            $this->playlistAsGuests()->associate($playlist);
        }

    }

    public function playlistAsGuests(): BelongsToMany
    {
        return $this->belongsToMany('App\Playlist', null, 'guests_ids', 'playlist_as_guest_ids');
    }

    public function getSpotifyId()
    {
        if ($this->isLinkedToSpotify()) {
            return $this->spotify_user_data['id'];
        }
        return "";
    }

    public function currentPlaylist()
    {
        $asHost = $this->playlists()->where('status', Playlist::STATUS_OPEN)->first();
        if ($asHost === null) {
            $asHost = $this->playlistAsGuests()->where('status', Playlist::STATUS_OPEN)->first();
        }
        return $asHost;
    }


    /**
     *
     * @throws SpotifyAccountNotLinkedException
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createAndOpenPlaylist(){

        return $playlist = $this->playlists()->create([
            'name' => "Edgar's playlist test!",
            'status' => Playlist::STATUS_OPEN,
            'spotify_data' => $this->getUserSpotifyApiAccess()->createUserPlaylist($this->getSpotifyId(),
                ['name' => config('app.name') . ' Playlist']
            )
        ]);
    }

    /**
     * Refresh user token if he has one which is expired
     */
    private function refreshSpotifyTokenIfNecessary()
    {
        // if user has a expired token, refresh it
        if ($this->isLinkedToSpotify() && $this->spotify_token_expiration - time() < 3) {

            // send refresh token request
            $session = SpotifyService::createSession();
            $session->refreshAccessToken($this->spotify_refresh_token);

            // update access token
            if ($session->getAccessToken() !== "") {
                $this->spotify_access_token = $session->getAccessToken();
                $this->spotify_token_expiration = $session->getTokenExpiration();
            }

            // update refresh access token
            if ($session->getRefreshToken() !== "") {
                $this->spotify_refresh_token = $session->getRefreshToken();
            }

            // bad to save inside model, but... you know... ;)
            $this->save();
        }
    }

    /**
     * Return spotify API
     *
     * @throws SpotifyAccountNotLinkedException
     * @return SpotifyWebAPI
     */
    public function getUserSpotifyApiAccess(): SpotifyWebAPI{

        if (!$this->isLinkedToSpotify()){
            throw new SpotifyAccountNotLinkedException();
        }

        // check token validity
        $this->refreshSpotifyTokenIfNecessary();

        // create API access
        $api = new SpotifyWebAPI();
        $api->setAccessToken($this->spotify_access_token);

        return $api;
    }

}
