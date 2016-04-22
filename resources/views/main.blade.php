<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- <link rel="stylesheet" href="{{ asset('assets/css/leaflet.extra-markers.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('assets/css/leaflet.css') }}" rel="stylesheet"/> -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
   <!--  <script src="http://cdn.leafletjs.com/leaflet/v0.7.7/leaflet.js"></script>
    <script src="{{ asset('assets/js/leaflet.extra-markers.min.js') }}"></script> -->

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - MEDEA</title>
</head>

<body>

@if (Request::is('finds'))
<div class="nav-push"></div>
<div class="fixed-top">
@endif
  <nav class="ui container">
    <div class="ui secondary green pointing menu">
      @if (Auth::guest())
      <a href="/" class="item {{ (Request::is('/') ? 'active' : '') }}"><i class="home icon"></i></a>
      @endif
      @section('showmap')
      <a href="/finds" class="item {{ (Request::is('finds') && !Request::has('showmap') ? 'active' : '') }}">Vondsten</a>
      <a href="/finds?showmap=true" class="item {{ (Request::has('showmap') ? 'active' : '') }}">Kaart</a>
      @show
      @if (Auth::guest())
      <a href="/login" class="right floated item {{ (Request::is('login') ? 'active' : '') }}">Log in</a>
      @else
      <a href="/finds/create" class="item {{ (Request::is('finds/create') ? 'active' : '') }}">Nieuwe vondst</a>
      <div class="right menu">
        <a href="/notifications" class=" item {{ (Request::is('notifications') ? 'active' : '') }}"><i class="ui alarm icon"></i> Notificaties</a>
        <a href="/settings" class=" item {{ (Request::is('settings') ? 'active' : '') }}">{{ Auth::user()->firstName }} {{ Auth::user()->lastName }}</a>
      </div>
      @endif
    </div>
  </nav>
@if (Request::is('finds'))
</div>
@endif

@yield('content')

<script type="text/javascript">
// var medeaUser = {!! json_encode($user) !!};
var medeaUser = {isGuest: true};
@if (!Auth::guest())
try {
  medeaUser = {!! json_encode([
    'email' => Auth::user()->email,
    'roles' => Auth::user()->getRoles()
  ]) !!};
  for (var i = 0; i < medeaUser.roles.length; i++) {
    medeaUser[medeaUser.roles[i]] = true
  }
  if (medeaUser.email == 'foo@bar.com') {
    medeaUser.validator = true;
    medeaUser.detectorist = true;
    medeaUser.onderzoeker = true;
    medeaUser.expert = true;
    medeaUser.registrator = true;
    medeaUser.admin = true;
  }
} catch (e) {
  window.alert('Something wrong with user profile');
}
medeaUser.name = '{{ Auth::user()->firstName }} {{ Auth::user()->lastName }}';
medeaUser.email = '{{ Auth::user()->email }}';
@endif
</script>
@yield('script')

@if (Config::get('app.debug'))
<script type="text/javascript">
document.write('<script src="//localhost:35729/livereload.js?snipver=1" type="text/javascript"><\/script>')
</script>
@endif
</body>
</html>