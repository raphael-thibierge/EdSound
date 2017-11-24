<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 24/11/2017
 * Time: 16:03
 */

namespace App;


interface TrackInterface
{

    /**
     * @return string track name
     */
    public function getName(): string;

    /**
     * @return array track artist list
     */
    public function getArtists();

    /**
     * @return mixed track album
     */
    public function getAlbum();

    /**
     * @return string track cover url
     */
    public function getCoverURL(): string ;

    /**
     * @return int track duration in milliseconds
     */
    public function getDurationMs(): int;

    /**
     * @return int track release year
     */
    public function getYear(): int;

}