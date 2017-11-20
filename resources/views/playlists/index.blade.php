@extends('layouts.bootstrap4')

@section('content')

    <h3>Spotify</h3>
    <div class="card-columns">
    @foreach($spotifyPlaylists->items as $playlist)
        <div class="card">
            <img class="card-img-top img-fluid" src="{{ $playlist->images[0]->url }}" alt="Card image cap">
            <div class="card-block">
                <h4 class="card-title">{{ $playlist->name }}</h4>
                <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                <a href="{{ $playlist->external_urls->spotify }}" class="btn btn-primary">Show</a>
            </div>
        </div>
    @endforeach
    </div>

@endsection