<?php
/**
 * Created by PhpStorm.
 * User: raphael
 * Date: 22/10/2017
 * Time: 12:08
 */

namespace App\Http\Services;


use App\Playlist;
use App\User;
use Illuminate\Support\Facades\Auth;
use SpotifyWebAPI\SpotifyWebAPI;
use SpotifyWebAPI\Session;

class SpotifyService
{


    public static function loginRequest(): string{

        $session = SpotifyService::createSession();
        $options = [
            'scope' => [
                'user-read-private',

                // playlists
                'playlist-read-private',
                'playlist-read-collaborative',
                'playlist-modify-public',
                'playlist-modify-private',
                // user infos
                'user-read-email',
                'user-read-birthdate',
                'user-top-read',
                // user's player
                'user-read-playback-state', // access to user's player
                'user-modify-playback-state',
                'user-read-currently-playing',
                'user-read-recently-played'
            ],
        ];

        return $session->getAuthorizeUrl($options);
    }


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

        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);

        return $api;
    }



    public static function createApiForUser(User $user): SpotifyWebAPI{
        $session = self::createSession();
        $session->refreshAccessToken($user->spotify_refresh_token);

        $user->spotify_access_token = $session->getAccessToken();

        echo $session->getTokenExpiration();
        $refreshToken = $session->getRefreshToken();
        //if (^e !== ""){

        //}
        $user->save();



        $api = new SpotifyWebAPI();
        $api->setAccessToken($user->spotify_access_token);
        return $api;
    }

    public static function loadUserPlaylists()
    {
        if(!Auth::check())
            return null;

        $user = Auth::user();

        $session = self::createSession();
        $session->refreshAccessToken($user->spotify_refresh_token);

        $user->spotify_access_token = $session->getAccessToken();

        $api = $user->getUserSpotifyApiAccess();

        return $api->getMyPlaylists();
    }

    public static function loadPlaylistTracks(Playlist $playlist)
    {
        if(!Auth::check())
            return null;

        $user = Auth::user();

        //$api = $user->getUserSpotifyApiAccess();

        $session = self::createSession();
        $session->refreshAccessToken($user->spotify_refresh_token);

        $user->spotify_access_token = $session->getAccessToken();

        $api = $user->getUserSpotifyApiAccess();
        $api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);

        return $api->getUserPlaylistTracks($playlist->getSpotifyOwner(), $playlist->getSpotifyId());
    }
}