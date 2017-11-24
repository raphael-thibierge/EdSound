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

    public function getName(): string
    {
        return $this->getTrackFromAppropriateSource()->getName();
    }

    public function getArtists()
    {
        return $this->getTrackFromAppropriateSource()->getArtists();
    }

    public function getAlbum()
    {
        return $this->getTrackFromAppropriateSource()->getAlbum();
    }

    public function getCoverURL(): string
    {
        return $this->getTrackFromAppropriateSource()->getCoverURL();
    }

    public function getDurationMs(): int
    {
        return $this->getTrackFromAppropriateSource()->getDurationMs();
    }

    public function getYear(): int
    {
        return $this->getTrackFromAppropriateSource()->getDurationMs();
    }
}
