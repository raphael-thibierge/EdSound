<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 22/10/2017
 * Time: 19:37
 */

namespace App\Exceptions;


use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

class SpotifyAccountNotLinkedException extends ResourceNotFoundException
{
    public function __construct($message = "Spotify account not linked", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }


}