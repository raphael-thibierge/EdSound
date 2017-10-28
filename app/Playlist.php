<?php

namespace App;

use App\Http\Services\SpotifyService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\EmbedsMany;

/**
 * @property array opened_at_dates
 * @property array closed_at_dates
 * @property string status
 * @property string spotify_data
 * @property User user
 */
class Playlist extends Model
{
    const STATUS_OPEN = 'open';
    const STATUS_CLOSE = 'close';


    protected $primaryKey = '_id';

    protected $collection = 'playlists';

    protected $fillable = [
        'created_by',
        '_id',
        'slug',
        'name',
        'status',
        'user_id',
         //['created', 'open', 'close'],
        'opened_at_dates',
        'closed_at_dates',
        'spotify_data'

    ];

    /**
     * Playlist's creator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Playlist's guests
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function guests(){
        return $this->belongsToMany('App\User', null, 'playlist_as_guest_ids', 'guests_ids');
    }


    /**
     * Playlist songs embed in playlist document
     *
     * @return EmbedsMany
     */
    public function songs(){
        return $this->embedsMany('App\Song');
    }


    /**
     * Open playlist set status to open and save de timestamp
     */
    public function open(){

        $this->status = self::STATUS_OPEN;
        // $this->opened_at_dates []= Carbon::now();

        // get creator
        $api = $this->user->getUserSpotifyApiAccess();

        // create the spotify playlist
        $this->spotify_data = $api->createUserPlaylist($this->user->getSpotifyId(),
            ['name' => Carbon::now()->toDateString()]
        );

    }

    /**
     * Open playlist set status to open and save de timestamp
     */
    public function close(){
        $this->status = self::STATUS_CLOSE;
        //$this->closed_at_dates []= Carbon::now();

    }

    public function addSong(string $trackId, User $submitter){

        $api = $this->user->getUserSpotifyApiAccess();

        $this->songs()->create([
            'submitter_id' => $submitter->id,
            'spotify_data' => $api->getTrack($trackId),
        ]);

        // add track to spotify playlist
        $api->addUserPlaylistTracks($this->user->getSpotifyId(), $this->getSpotifyId(), [$trackId]);

    }


    public function getOpenedAtDates(): Collection{
        return collect($this->opened_at_dates);
    }

    public function getClosedAtDates(): Collection{
        return collect($this->closed_at_dates);
    }

    public function getSpotifyId(){

        if (isset($this->spotify_data)){
            return $this->spotify_data['id'];
        }
        return '';
    }


}
