@extends('layouts.bootstrap4')

@section('content')
    <h1>Account</h1>
    <div class='col-lg-6 col-md-8'>
        @if(Session::has('error'))
            <div class="alert alert-danger">

            </div>
        @endif
        @if(Session::has('success'))
            <div class="alert alert-success">
                <strong>Success!</strong>{{ Session::get('success') }}
            </div>
        @endif
        <form>
            <h3>General informations</h3>
            <div class="form-group">
                <label for="inputEmail">E-mail</label>
                <input type="email" class="form-control" id="inputEmail" aria-describedby="emailHelp" placeholder="Enter email" value="{{ $user->email }}">
            </div>

            <div class="form-group">
                <label for="inputEmail">Login</label>
                <input type="email" class="form-control" id="inputEmail" aria-describedby="emailHelp" placeholder="Enter email" value="{{ $user->name }}">
            </div>

            <h3>Platform information</h3>
            <div class="form-group">
                <label for="inputEmail">Facebook</label>
                <input type="email" class="form-control" id="inputEmail" aria-describedby="emailHelp" placeholder="Enter email">
                <small id="emailHelp" class="form-text text-muted">We use your Facebook account to connect your accounts.</small>
            </div>

            <div class="form-group">
                <label for="inputEmail">Spotify</label>
                <div class="row">
                    @if($user->isLinkedToSpotify())
                        <div class="col-lg-6 col-md-4 col-sm-8 col-sm-offset-2">
                            <p class="text-center">
                                <img src="{{ $user->getSpotifyUserData()["images"][0]["url"] }}" class="img-fluid" alt="">
                            </p>
                        </div>
                        <div class="col-lg-6 col-md-8 col-sm-offset-4 col-sm-8">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <p><strong>{{ $user->getSpotifyUserData()["display_name"] }}</strong></p>
                                    <p><em>{{ $user->getSpotifyUserData()["email"] }}</em></p>
                                </div>
                                <div class="col-lg-12 col-md-12 col-sm-12">
                                    <a href="#" class="btn btn-danger">Disconnect</a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-lg-6 col-md-8">
                            <p><em>Connect your Spotify account</em></p>
                        </div>
                        <div class="col-lg-offset-3 col-lg-2 col-md-offset-1 col-md-2">
                            <a class="btn btn-success" href="{{ $spotifyLoginURL }}" role="button">Connect</a>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection