<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">


    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-106873842-3"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-106873842-3');
    </script>


</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark navbar-light bg-faded">
        <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavAltMarkup">

                <ul class="nav navbar-nav mr-auto">
                    <li class="nav-item {{ request()->getPathInfo() == "/" ? 'active' : '' }}">
                        <a class="nav-item nav-link" href="{{ route('home') }}">Home <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item {{ strstr(request()->getPathInfo(), "/news") == true ? 'active' : '' }}">
                        <a class="nav-item nav-link" href="#">News</a>
                    </li>
                    <li class="nav-item {{ strstr(request()->getPathInfo(), "/playlists") == true ? 'active' : '' }}">
                        <a class="nav-item nav-link" href="{{ route('playlists.index') }}">Playlists</a>
                    </li>
                </ul>
                <ul class="nav navbar-nav ml-auto">
                    @if (Route::has('login') && Auth::check() === false)
                        <li class="nav-item {{ strstr(request()->getPathInfo(), "/login") == true ? 'active' : '' }}"><a class="nav-item nav-link " href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item {{ strstr(request()->getPathInfo(), "/register") == true ? 'active' : '' }}"><a class="nav-item nav-link" href="{{ route('register') }}">Register</a></li>
                    @else
                        <li class="nav-item {{ strstr(request()->getPathInfo(), "/account") == true ? 'active' : '' }}">
                            <a class="nav-item nav-link" href="{{ route('user.account') }}">Account</a>
                        </li>

                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST">
                                {!! csrf_field() !!}
                                <button type="submit" role='button' class="btn btn-link nav-item nav-link">
                                    Logout
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>

{{--
<script src="{{ asset('js/app.js') }}"></script>
--}}

<footer>

</footer>
</body>

</html>
