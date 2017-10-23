<?php

namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SpotifyWebAPI\SpotifyWebAPI;

class SpotifyController extends Controller
{

    /**
     * SpotifyController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function login(Request $request){

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

        return redirect($session->getAuthorizeUrl($options));

    }

    public function callback(Request $request){
        $session = SpotifyService::createSession();

        // Request a access token using the code from Spotify
        $session->requestAccessToken($request->get('code'));

        $accessToken = $session->getAccessToken();
        $refreshToken = $session->getRefreshToken();

        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);
        $api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);

        // store user data
        $user = Auth::user();
        $user->spotify_access_token = $accessToken;
        $user->spotify_refresh_token = $refreshToken;
        $user->spotify_user_data = $api->me();
        $user->save();

        return view('spotify-success');
    }
}
