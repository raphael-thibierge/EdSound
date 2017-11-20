<?php

namespace App;


use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property mixed spotify_data
 * @property mixed added_by
 * @property mixed added_at
 * @property mixed spotifyId
 * @property mixed name
 * @property mixed duration
 * @property mixed url_preview
 */
class Track extends Model
{

    protected $collection = 'tracks';

    protected $fillable = [
        'added_by',
        'added_at',
        'name',
        'duration',
        'url_preview',

        'spotifyId',
        'spotify_data',

        // Pour les 2 champs ci-dessous, à utiliser pour l'association Track/Playlist, type N-N
        //'downvotes',
        //'played_at',


    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'humanDuration',
    ];

    protected $dates = [
        'played_at'
    ];

    public function artists(){
        return $this->hasMany('App\Artist', '_id', '_id');
    }

    public function albums() {
        return $this->belongsToMany('App\Albums', null, '_id', '_id');
    }

    public function submitter(){
        return $this->belongsTo('App\User');
    }

    /**
     * Accessors
     */
    public function getName(){
        return $this->spotify_data['name'];

    }

    public function getSpotidyId(){
        return $this->spotify_data['id'];
    }

    public function getDuration():int {
        return (int)($this->spotify_data['duration_ms']/1000);
    }

    public function getDurationToHuman(): string {
        $initial = $this->getDuration();
        $seconds = $initial % 60;
        $minutes = ($initial - $seconds ) / 60;
        return $minutes . ':' . $seconds;
    }

    public function getHumanDurationAttribute(){
        return $this->getDurationToHuman();
    }

    public function status(){

        if (!isset($this->played_at) || empty($this->played_at)){
            return 'not_played';
        } else {
            return $this->played_at->addSeconds($this->getDuration()) > Carbon::now() ?
                'playing' : 'played';
        }

    }


}
