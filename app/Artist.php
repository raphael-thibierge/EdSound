<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    protected $collection = 'artists';

    protected $primaryKey = '_id';

    protected $fillable = [
        'name',
        'type',

        'spotifyId',
        'spotify_data',
    ];

    public function tracks() {
        return $this->hasMany('App\Tracks', '_id','_id');
    }

    public function albums() {
        return $this->hasMany('App\Album', '_id','_id');
    }

}
