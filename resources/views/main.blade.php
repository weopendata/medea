<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - MEDEA</title>
</head>

<body>

@section('nav')
<div class="nav-top">
@show
  <nav class="ui container">
    <div class="ui secondary green pointing menu">
      <a href="/" class="item {{ Request::is('/') ? 'active' : '' }}">Home</a>
      <a href="/finds" class="item {{ Request::is('finds') ? 'active' : '' }}">Vondsten</a>
      @if (!Auth::guest())
      <a href="/persons" class="item {{ Request::is('persons') ? 'active' : '' }}">Leden</a>
      <a href="/finds/create" class="item {{ (Request::is('finds/create') ? 'active' : '') }}" data-step="2" data-intro="Klik hier om een nieuwe vondst te registreren." id="findsCreate">Nieuwe vondst</a>
      @endif

      <div class="right menu">
        <a href="{{ env('CMS', 'http://medea-cms.weopendata.com') }}" class="item {{ Request::is('about') ? 'active' : '' }}">Over MEDEA</a>
        <a href="#" class="item {{ Request::is('help') ? 'active' : '' }}" onclick="startIntro();return false">Handleiding</a>
        @if (Auth::guest())
        <a href="/login" class="right floated item {{ (Request::is('login') ? 'active' : '') }}">Log in</a>
        @else
        <div class="ui top right pointing dropdown link item item-notif">
          <span class="text"><span class="ui red circular label" v-if="notifUnread" v-text="notifUnread" v-cloak></span> Meldingen</span>
          <i class="dropdown icon"></i>
          <div class="menu">
            <div v-if="notifications&&notifications.length" v-cloak>
              <div class="item" v-for="n in notifications" v-text="n.message" :class="{read:n.read}" @click.stop="notifGo(n, $index)"></div>
            </div>
            <div v-else class="item" @click.stop>Er zijn geen meldingen</div>
          </div>
        </div>
        <div class="ui top right pointing dropdown link item">
          <span class="text">{{ Auth::user()->firstName }}</span>
          <i class="dropdown icon"></i>
          <div class="menu">
            <div class="header">Profiel</div>
            <a href="/persons/{{ Auth::user()->id }}" @click.stop class="item">Profiel bekijken</a>
            <a href="/settings" @click.stop class="item">Profiel aanpassen</a>
            <div class="divider"></div>
            <a href="/settings" @click.stop class="item">Instellingen</a>
            <a href="/logout" @click.stop class="item">Afmelden</a>
          </div>
        </div>
        @endif
      </div>
    </div>
  </nav>
</div>

@yield('content')

<br style="clear:both;">
<footer>
  <div class="ui container">
    <div style="float:right">
      <a href="mailto:pdeckers@vub.ac.be">Contact</a> &nbsp; &middot; &nbsp;
      <a href="/feedback">Feedback</a>
    </div>
    <p>
      <a href="/voorwaarden">Gebruikersvoorwaarden</a>
    </p>
    <p>
      <a href="https://creativecommons.org/licenses/by-nc/4.0/">
        <img src="https://licensebuttons.net/l/by-nc/4.0/80x15.png" style="vertical-align:middle">
        <span style="opacity:.5;font-size:12px">We gebruiken een open content licentie</span>
      </a>
    </p>
  </div>
</footer>

<script type="text/javascript">
_paq = [];
var medeaUser = {isGuest: true};
@if (!Auth::guest())
try {
  medeaUser = {!! json_encode([
    'savedSearches' => Auth::user()->savedSearches,
    'id' => Auth::user()->id,
    'email' => Auth::user()->email,
    'roles' => Auth::user()->getRoles()
  ]) !!};
  for (var i = 0; i < medeaUser.roles.length; i++) {
    medeaUser[medeaUser.roles[i]] = true
  }
} catch (e) {
  window.alert('Something wrong with user profile');
}
medeaUser.name = '{{ Auth::user()->firstName }} {{ Auth::user()->lastName }}';
medeaUser.email = '{{ Auth::user()->email }}';
@endif
</script>
<script src="/js/vendor.min.js?16nov"></script>

@section('script')
<script src="{{ asset('js/users-admin.js') }}"></script>
@show

<script type="text/javascript">
$('nav .ui.dropdown').dropdown()
</script>

@if (Config::get('app.debug'))
<script type="text/javascript">
document.write('<script src="//localhost:35729/livereload.js?snipver=1" type="text/javascript"><\/script>')
</script>
@endif
</body>
</html>