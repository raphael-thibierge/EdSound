@extends('layouts.bootstrap4')

@section('style')
    @parent
    <!-- Fichier scss à gérer plus part pour le nombre de colonne en fonction de la taille d'écran -->
    <!--link href="{{ asset('css/common.scss') }}" rel="stylesheet"-->
@stop

@section('content')
    <hr>
    <h3>Spotify</h3>
    <hr>



    <div class="card-columns">
    @foreach($tPlaylists as $playlist)
        <div class="card">
            <img class="card-img-top img-fluid" src="{{ $playlist->url_image }}" alt="Card image cap">
            <div class="card-block">
                <h4 class="card-title">{{ $playlist->name }}</h4>
                <a href="{{ url('/playlist', $playlist->id) }}" class="btn btn-primary">Edit</a>
                <a href="#" class="btn btn-primary">Share</a>
                <a href="{{ $playlist->url_platform }}" class="btn btn-primary" target="_blank">Show</a>
            </div>
        </div>
    @endforeach
    </div>
@endsection