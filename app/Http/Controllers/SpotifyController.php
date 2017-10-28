<?php

namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
use App\User;
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
        //$this->middleware('auth');
    }

    public function login(User $user){

        session()->flash('user_id', $user->id);

        return redirect(SpotifyService::loginRequest());
    }

    public function callback(Request $request){
        $session = SpotifyService::createSession();

        // Request a access token using the code from Spotify
        $session->requestAccessToken($request->get('code'));

        $accessToken = $session->getAccessToken();
        $refreshToken = $session->getRefreshToken();
        $expiration = $session->getTokenExpiration();

        $api = new SpotifyWebAPI();
        $api->setAccessToken($accessToken);
        $api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);

        // store user data


        $user_id = session()->get('user_id');
        $user = User::find($user_id);
        $user->spotify_access_token = $accessToken;
        $user->spotify_refresh_token = $refreshToken;
        $user->spotify_token_expiration = $expiration;
        $user->spotify_user_data = $api->me();
        $user->save();

        return view('spotify-success');
    }
}
