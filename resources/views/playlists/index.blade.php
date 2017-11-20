@extends('layouts.bootstrap4')

@section('content')

    @foreach($playlists->items as $playlist)
        <h3>{{ $playlist->name }}</h3>
        <hr>
    @endforeach

@endsection