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

    protected $collection = 'playlists';

    protected $primaryKey = '_id';

    protected $fillable = [
        'created_by',
        'name',
        'slug',
        'status',
        'user_id',
        'url_image',
        'url_platform',

        //['created', 'open', 'close'],
        'opened_at_dates',
        'closed_at_dates',

        'spotifyId',
        'spotify_data',
    ];

    /**
     * Playlist's creator
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User', '_id','_id');
    }

    /**
     * Playlist's guests
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function guests() {
        return $this->belongsToMany('App\User', null, 'playlist_as_guest_ids', 'guests_ids');
    }


    /**
     * Playlist songs embed in playlist document
     *
     * @return EmbedsMany
     */
    public function songs(){
        return $this->embedsMany('App\Track');
    }


    /**
     * Open playlist set status to open and save de timestamp
     */
    public function open(){

        $this->status = self::STATUS_OPEN;
        // $this->opened_at_dates []= Carbon::now();

    }

    /**
     * Open playlist set status to open and save de timestamp
     */
    public function close(){
        $this->status = self::STATUS_CLOSE;
        //$this->closed_at_dates []= Carbon::now();

    }

    public function addSong(string $trackId, User $submitter){

        $api = SpotifyService::load();

        $this->songs()->create([
            'submitter_id' => $submitter->id,
            'spotify_data' => $api->getTrack($trackId),
        ]);

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

    public function getSpotifyOwner(){

        if (isset($this->spotify_data)){
            return $this->spotify_data['owner']['id'];
        }
        return '';
    }

    public function currentSong(){
        return $this->user->getUserSpotifyApiAccess()->getMyCurrentTrack();
    }


}
