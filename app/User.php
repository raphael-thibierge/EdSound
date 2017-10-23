<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Relations\BelongsToMany;
use Jenssegers\Mongodb\Relations\HasMany;

/**
 * @property string spotify_access_token
 * @property string messenger_sender_id
 * @property array asGuestPlaylists
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


    public function isLinkedToSpotify(): bool {
        return isset($this->spotify_access_token) && !empty($this->spotify_access_token);
    }

    public function playlists() : HasMany{
        return $this->HasMany('App\Playlist');
    }


    public function merge( User &$secondUser)
    {

        // mege attributes
        foreach ($this->fillable as $field){
            if ($this->getAttribute($field) === null
                && $secondUser->getAttribute($field) !== $this->getAttribute($field)){
                $this->setAttribute($field, $secondUser->getAttribute($field));
            }
        }

        // merge playlists
        $secondUser->playlists()->update(['created_by_user_id' => $secondUser]);

        // merge playlists as guests
        foreach ($secondUser->playlist_as_guest_ids as $playlist){
            $secondUser->playlistAsGuests()->dissociate($playlist);
            $this->playlistAsGuests()->associate($playlist);
        }

    }

    public function playlistAsGuests(): BelongsToMany{
        return $this->belongsToMany('App\Playlist',  null, 'guests_ids', 'playlist_as_guest_ids');
    }

    public function getSpotifyId(){
        if ($this->isLinkedToSpotify()){
            return $this->spotify_user_data['id'];
        }
        return "";
    }

    public function currentPlaylist(){
        $asHost = $this->playlists()->where('status', Playlist::STATUS_OPEN)->first();
        if ($asHost === null){
            $asHost = $this->playlistAsGuests()->where('status', Playlist::STATUS_OPEN)->first();
        }
        return $asHost;
    }

}
