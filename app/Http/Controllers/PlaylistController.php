<?php

namespace App\Http\Controllers;

use App\Http\Services\SpotifyService;
use App\Playlist;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlaylistController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function data(Playlist $playlist){
        return $this->successResponse([
            'playlist' => $playlist
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $spotifyPlaylists = SpotifyService::loadUserPlaylists();

        foreach ($spotifyPlaylists->items as $p) {
            if(($playlist = Playlist::where('spotifyId', '=', $p->id))->count() > 0){

            } else {
                $playlist = new Playlist;
                $playlist->created_by = Auth::user()->getAuthIdentifier();
                $playlist->name = $p->name;
                $playlist->slug = str_slug($playlist->name, '-');
                $playlist->url_image = $p->images[0]->url;
                $playlist->url_platform = $p->external_urls->spotify;
                $playlist->spotifyId = $p->id;
                $playlist->spotify_data = [
                    'id' => $p->id,
                    'owner' => $p->owner->id,
                ];

                $playlist->save();
            }

            $tPlaylists[] = $playlist->first();
        }

        return view('playlists.index', compact('tPlaylists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $playlist = Playlist::find($id);

        $tracklist = SpotifyService::loadPlaylistTracks($playlist);

        return view('playlists.show', compact('playlist', 'tracklist'));
        /*
        return view('playlists.show', [
            'playlist' => $playlist
        ]);
        */
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function edit(Playlist $playlist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Playlist $playlist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Playlist  $playlist
     * @return \Illuminate\Http\Response
     */
    public function destroy(Playlist $playlist)
    {
        //
    }
}