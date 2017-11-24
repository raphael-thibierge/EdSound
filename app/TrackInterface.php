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

    public function getName(): string;

    public function getArtists();

    public function getAlbum();

    public function getCoverURL(): string ;

    public function getDurationMs(): int;

    public function getYear(): int;

}