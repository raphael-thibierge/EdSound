<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
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

    public function artists() {
        return $this->belongsToMany('App\Artists', null, '_id', '_id', null);
    }
}
