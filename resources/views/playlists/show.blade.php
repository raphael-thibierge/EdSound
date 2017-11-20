@extends('layouts.bootstrap4')


@section('content')

    <div class="row">
        <a href="{{ url('/playlist') }}" class="btn btn-primary">Back</a>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-3">
            <img class="card-img-top img-fluid" src="{{ $playlist->url_image }}" alt="Card image cap">
        </div>
        <div class="col-lg-8 col-md-8 col-sm-9">
            <h3>{{ $playlist->name }}</h3>
        </div>
    </div>
    <div class="row">
        <ul class="list-group">
            @foreach($tracklist->items as $track)
                <li class="list-group-item">{{ $track->track->name }}</li>
            @endforeach
        </ul>
    </div>
@endsection