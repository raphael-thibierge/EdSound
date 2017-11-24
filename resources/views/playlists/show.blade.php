@extends('layouts.bootstrap4')


@section('content')
    <hr>
    <div class="row">
        <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('playlists.index') }}" class="btn btn-primary">Back</a>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6">
            <h3>Playlist</h3>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-9">
            <ul class="list-group">
                @foreach($tracklist as $track)
                    <li class="list-group-item">{{ $track->getName() }}</li>
                @endforeach
            </ul>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-3">
            <div class="row">
                <img class="card-img-top img-fluid" src="{{ $playlist->url_image }}" alt="Card image cap">
            </div>
            <div class="row">
                <h3>{{ $playlist->name }}</h3>
            </div>
        </div>

    </div>
    <div class="row">

    </div>
@endsection