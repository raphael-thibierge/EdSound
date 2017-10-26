<?php

namespace App;


use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property mixed spotify_data
 */
class Song extends Model
{


    protected $fillable = [
        'submitter_id',
        'spotify_data',
        'upvotes',
        'downvotes',

    ];


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

    public function getDurationMs():int {
        return $this->spotify_data['duration_ms'];
    }

    public function getDurationToHuman(): string {
        $initial = (int)($this->getDurationMs()/1000);
        $seconds = $initial % 60;
        $minutes = ($initial - $seconds ) / 60;
        return $minutes . ':' . $seconds;
    }


}
