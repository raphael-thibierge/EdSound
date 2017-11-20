<?php

namespace App\Http\Controllers;

use App\Album;
use App\Artist;
use App\Http\Services\SpotifyService;
use App\Playlist;
use App\Track;
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
                $playlist = $playlist->first();
            } else {

                $playlist = new Playlist;
                $playlist->created_by = Auth::user()->getAuthIdentifier();
                $playlist->name = $p->name;
                $playlist->slug = str_slug($playlist->name, '-');
                $playlist->url_image = $p->images[0]->url;
                $playlist->url_platform = $p->external_urls->spotify;
                $playlist->spotifyId = $p->id;
                $playlist->spotify_data = $p;

                $playlist->save();
            }

            $tPlaylists[] = $playlist;
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
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $playlist = Playlist::find($id);

        $spotifyTracklist = SpotifyService::loadPlaylistTracks($playlist);

        foreach ($spotifyTracklist->items as $t) {
            if(($track = Track::where('spotifyId', '=', $t->track->id))->count() > 0){
                $track = $track->first();
            } else {

                $track = new Track;
                $track->added_by = $t->added_by;
                $track->added_at = $t->added_at;
                $track->spotifyId = $t->track->id;
                $track->name = $t->track->name;
                $track->duration = $t->track->duration_ms;
                $track->url_preview = $t->track->preview_url;

                $track->spotify_data = $t;


                foreach ($t->track->artists as $a) {
                    $artist = new Artist;
                    // create artist
                }

                foreach ($t->track->album as $a) {
                    $album = new Album();
                    //create album
                }

                ///$playlist->save();
            }

            $tracklist[] = $track;

        }

        //dd($playlist, $tracklist);
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