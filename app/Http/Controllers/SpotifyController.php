<?php

namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
use App\OAuthConnect;
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

        // store user data
        if(session()->has('user_id')) {
            $user_id = session()->get('user_id');
            $request->session()->forget('user_id');
        } else if(Auth::user()) {
            $user_id = Auth::user()->getAuthIdentifier();
        } else {
            throw new \Exception("User is guest");
        }
        $user = User::find($user_id);


        $session = SpotifyService::createSession();
        // Request a access token using the code from Spotify
        $session->requestAccessToken($request->get('code'));

        //
        $api = new SpotifyWebAPI();
        $api->setReturnType(SpotifyWebAPI::RETURN_ASSOC);
        $api->setAccessToken($session->getAccessToken());


        $user->OAuthConnects()->save(new OAuthConnect([
            'service' => OAuthConnect::SPOTIFY,
            'access_token'  => $session->getAccessToken(),
            'refresh_token' => $session->getRefreshToken(),
            'token_expiration' => $session->getTokenExpiration(),
            'user_data' => $api->me()
        ]));



        if(session()->has('redirect_botman')) {
            $request->session()->forget('redirect_botman');
            return view('spotify.spotify-success');
        } else {
            return redirect('/account')->with('success','You are connected with Spotify');
        }
    }
}
