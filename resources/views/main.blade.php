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

@if (Request::is('/'))
<div class="nav-home">
@else
<div class="nav-top">
@endif
  <nav class="ui container">
    <div class="ui secondary green pointing menu">
      <a href="/" class="item {{ Request::is('/') ? 'active' : '' }}">Home</a>
      <a href="/finds" class="item {{ Request::is('finds') ? 'active' : '' }}">Vondsten</a>
      @if (!Auth::guest())
      <a href="/users" class="item {{ Request::is('users') ? 'active' : '' }}">Leden</a>
      <a href="/finds/create" class="item {{ (Request::is('finds/create') ? 'active' : '') }}">Nieuwe vondst</a>
      @endif

      <div class="right menu">
        <a href="/about" class="item {{ Request::is('about') ? 'active' : '' }}">Over MEDEA</a>
        <a href="/help" class="item {{ Request::is('help') ? 'active' : '' }}">Handleiding</a>
        @if (Auth::guest())
        <a href="/login" class="right floated item {{ (Request::is('login') ? 'active' : '') }}">Log in</a>
        @else
        <div class="ui top right pointing dropdown link item">
          <span class="text">Meldingen</span>
          <i class="dropdown icon"></i>
          <div class="menu">
            <div class="item">Vondst #424389 werd gepubliceerd</div>
            <div class="item">Vondst #379407 werd gepubliceerd</div>
          </div>
        </div>
        <div class="ui top right pointing dropdown link item">
          <span class="text">{{ Auth::user()->firstName }}</span>
          <i class="dropdown icon"></i>
          <div class="menu">
            <div class="header">Profiel</div>
            <a href="/users/{{ Auth::user()->id }}" class="item">Profiel bekijken</a>
            <a href="/settings" class="item">Profiel aanpassen</a>
            <div class="divider"></div>
            <a href="/settings" class="item">Instellingen</a>
            <a href="/logout" class="item">Afmelden</a>
          </div>
        </div>
        @endif
      </div>
    </div>
  </nav>
</div>

@yield('content')

<footer>
  <hr>
  <div class="ui container">
    <p>&nbsp;</p>
    <a href="https://creativecommons.org/licenses/by-nc/4.0/">
      We gebruiken een open content licentie<br>
      <img src="https://licensebuttons.net/l/by-nc/4.0/80x15.png">
    </a>
  </div>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
</footer>

<script type="text/javascript">
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
  if (medeaUser.administrator) {
    medeaUser.validator = true;
    medeaUser.detectorist = true;
    medeaUser.onderzoeker = true;
    medeaUser.vondstexpert = true;
    medeaUser.registrator = true;
    medeaUser.administrator = true;
  }
} catch (e) {
  window.alert('Something wrong with user profile');
}
medeaUser.name = '{{ Auth::user()->firstName }} {{ Auth::user()->lastName }}';
medeaUser.email = '{{ Auth::user()->email }}';
@endif
</script>
<script src="/js/vendor.min.js"></script>
<script type="text/javascript">
$('nav .ui.dropdown').dropdown()
</script>
@yield('script')

@if (Config::get('app.debug'))
<script type="text/javascript">
document.write('<script src="//localhost:35729/livereload.js?snipver=1" type="text/javascript"><\/script>')
</script>
@endif
</body>
</html>