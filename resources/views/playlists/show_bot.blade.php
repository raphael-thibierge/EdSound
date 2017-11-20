@extends('layouts.bootstrap4')

@section('content')
<div class="container">

    <div id="app-playlist"></div>

</div>

    <script type="application/javascript">
        window.app = {
            name: 'playlist.mobile',
            props: {
                ajax_route: '{{ route('playlist.data', ['playlist' => $playlist]) }}'
            },
        }
    </script>


@endsection
