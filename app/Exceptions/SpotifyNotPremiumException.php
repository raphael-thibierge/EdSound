<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 22/10/2017
 * Time: 19:46
 */

namespace App\Exceptions;


use Symfony\Component\Finder\Exception\AccessDeniedException;
use Throwable;

class SpotifyNotPremiumException extends AccessDeniedException
{
    public function __construct($message = "user is not a spotify premium member", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}