<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="stylesheet" href="{{ asset('assets/css/leaflet.extra-markers.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/leaflet.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" href="{{ asset('assets/css/semantic.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
    <script src="{{ asset('assets/js/leaflet.extra-markers.min.js') }}"></script>
    <script src="{{ asset('assets/js/semantic.min.js') }}"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MEDEA</title>
</head>

<body>
<div class="ui page grid">
    <div class="computer tablet only row">
        <div class="ui green menu navbar">
            <a href="" class="active item"><i class="home icon"></i></a>
            <a href="/finds" class="item">Vondsten</a>
            <a href="/finds/create" class="item">Nieuwe vondst</a>
            <a href="login" class="right floated item">Log in</a>
        </div>
    </div>
</div>
<div class="container">
  @yield('content')
</div>
@yield('script')

@if (Config::get('app.debug'))
<script type="text/javascript">
document.write('<script src="//localhost:35729/livereload.js?snipver=1" type="text/javascript"><\/script>')
</script> 
@endif
</body>
</html>