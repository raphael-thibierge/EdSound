<?php

namespace App;


use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string name
 * @property array artists
 * @property mixed album
 * @property int duration_ms
 */
class TrackSpotify extends Model implements TrackInterface
{

    protected $primaryKey = '_id';

    protected $fillable = [
        "album",
        "artists",
        "available_markets",
        "disc_number",
        "duration_ms",
        "explicit",
        "external_ids",
        "external_urls",
        "href",
        "id",
        "name",
        "popularity",
        "preview_url",
        "track_number",
        "type",
        "uri",
    ];

    public function getName(): string
    {
        return $this->name;
    }

    public function getArtists()
    {
        return $this->artists;
    }

    public function getAlbum()
    {
        return $this->album['name'];
    }

    public function getCoverURL(): string
    {
        return $this->album['images'][0]['url'];
    }

    public function getDurationMs(): int
    {
        return (int)($this->duration_ms/1000);
    }

    public function getYear(): int
    {
        return -1;
    }
}
