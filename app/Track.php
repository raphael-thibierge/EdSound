<?php

namespace App;


use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\EmbedsOne;

/**
 *
 * @property TrackSpotify spotifyTrack
 */
class Track extends Model implements TrackInterface
{
    protected $collection = 'tracks';

    protected $fillable = [];

    public function spotifyTrack(): EmbedsOne{
        return $this->embedsOne('App\TrackSpotify');
    }

/*    public function getDurationToHuman(): string {
        $initial = $this->getDurationMs();
        $seconds = $initial % 60;
        $minutes = ($initial - $seconds ) / 60;
        return $minutes . ':' . $seconds;
    }
*/

    public function getTrackFromAppropriateSource(): TrackInterface {

        // TODO choose appropriate track from spotify, soundcloud, etc..

        return $this->spotifyTrack;
    }

    /**
     * @return string track name
     */
    public function getName(): string
    {
        return $this->getTrackFromAppropriateSource()->getName();
    }

    /**
     * @return array track artist list
     */
    public function getArtists()
    {
        return $this->getTrackFromAppropriateSource()->getArtists();
    }

    /**
     * @return mixed track album
     */
    public function getAlbum()
    {
        return $this->getTrackFromAppropriateSource()->getAlbum();
    }

    /**
     * @return string track cover url
     */
    public function getCoverURL(): string
    {
        return $this->getTrackFromAppropriateSource()->getCoverURL();
    }

    /**
     * @return int track duration in milliseconds
     */
    public function getDurationMs(): int
    {
        return $this->getTrackFromAppropriateSource()->getDurationMs();
    }

    /**
     * @return int track release year
     */
    public function getYear(): int
    {
        return $this->getTrackFromAppropriateSource()->getYear();
    }

}
