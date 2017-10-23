<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 22/10/2017
 * Time: 12:08
 */

namespace App\Http\Services;


use App\User;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

class SpotifyService
{

    public static function createSession(): Session {
        return  new Session(
            config('services.spotify.key'),
            config('services.spotify.secret'),
            env('APP_URL') . "/spotify/callback"
        );
    }

//"product": "open" --> no premium
//"product": "premium"

    public static function load(): SpotifyWebAPI
    {
        // create api session
        $session = self::createSession();

        // get access token
        $session->requestCredentialsToken();
        $accessToken = $session->getAccessToken();

        // Fetch the saved access token from somewhere. A database for example.

        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);

        return $api;
    }

    public static function createApiForUser(User $user): SpotifyWebAPI{
        $session = self::createSession();
        $api = new SpotifyWebAPI();
        $api->setAccessToken($user->spotify_access_token);
        return $api;
    }
}