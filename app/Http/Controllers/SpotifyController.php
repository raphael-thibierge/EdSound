<?php

namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
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
        session()->flash('redirect_botman', strstr(URL::previous(),'botman'));

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
        if(session()->get('user_id')) {
            $user_id = session()->get('user_id');
        } else if(Auth::user()) {
            $user_id = Auth::user()->getAuthIdentifier();
        } else {
            // exception
        }


        $user = User::find($user_id);
        $user->spotify_access_token = $accessToken;
        $user->spotify_refresh_token = $refreshToken;
        $user->spotify_token_expiration = $expiration;
        $user->spotify_user_data = $api->me();
        $user->save();

        $request->session()->forget('user_id');

        $redirect_botman = session()->get('redirect_botman');
        $request->session()->forget('redirect_botman');

        if($redirect_botman) {
            return view('spotify.spotify-success');
        } else {
            return redirect('/account')->with('success','You are connected with Spotify');
        }
    }
}
