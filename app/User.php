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
 * @property string current_playlist_id
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
        'playlist_as_guest_ids',
        'current_playlist_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function spotifyConnect(){
        return $this->OAuthConnects()->where('service', OAuthConnect::SPOTIFY)->first();
    }

    public function getSpotifyUserData(){
        return $this->spotifyConnect()['user_data'];
    }

    public function isLinkedToSpotify(): bool
    {
        return $this->spotifyConnect() !== null;
    }

    public function playlists(): HasMany
    {
        return $this->HasMany('App\Playlist', '_id','_id');
    }

    public function OAuthConnects(){
        return $this->embedsMany('App\OAuthConnect');
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

    public function setCurrentPlaylist($id){
        $this->current_playlist_id = $id;
    }

    public function currentPlaylist()
    {
        if (isset($this->current_playlist_id) && !empty($this->current_playlist_id) && $this->current_playlist_id !== null){
            return Playlist::find($this->current_playlist_id);
        }
        return null;
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
        ]);
    }

    /**
     * Refresh user token if he has one which is expired
     */
    private function refreshSpotifyTokenIfNecessary()
    {
        // if user has a expired token, refresh it
        if ($this->isLinkedToSpotify() && $this->spotifyConnect()->token_expiration- time() < 3) {

            // send refresh token request
            $session = SpotifyService::createSession();
            $session->refreshAccessToken($this->spotifyConnect()->refresh_token);

            // update access token
            if ($session->getAccessToken() !== "") {

                $this->spotifyConnect()->update([
                    'access_token' => $session->getAccessToken(),
                    'refresh_token' => $session->getTokenExpiration(),
                ]);

            }

            // update refresh access token
            if ($session->getRefreshToken() !== "") {
                $this->spotifyConnect()->update([
                    'refresh_token' => $session->getTokenExpiration(),
                ]);
            }

            // bad to update inside model, but... you know... ;)
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
        $api->setAccessToken($this->spotifyConnect()->access_token);

        return $api;
    }

}
