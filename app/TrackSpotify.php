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


    /**
     * Attributes return from spotify API
     * Check https://developer.spotify.com/web-api/get-track/ for more details
     *
     * @var array
     */
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

    /**
     * @return string track name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array track artist list
     */
    public function getArtists()
    {
        return $this->artists;
    }

    /**
     * @return mixed track album
     */
    public function getAlbum()
    {
        return $this->album;
    }

    /**
     * @return string track cover url
     */
    public function getCoverURL(): string
    {
        return $this->album['images'][0]['url'];
    }

    /**
     * @return int track duration in milliseconds
     */
    public function getDurationMs(): int
    {
        return (int)($this->duration_ms/1000);
    }

    /**
     * @return int track release year
     */
    public function getYear(): int
    {
        return -1;
    }
}
