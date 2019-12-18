<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?{{ Config::get('app.version') }}"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/2.9.3/introjs.min.css" />
    <script src="https://maps.googleapis.com/maps/api/js?key={{env('GOOGLE_API_KEY')}}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - MEDEA</title>
    @if (!empty($meta))
      @foreach ($meta as $property => $content)
        <meta property="{{$property}}" content="{{$content}}">
      @endforeach
    @endif
</head>

<body>
<script src="https://cdn.jsdelivr.net/npm/intro.js@2.9.3/intro.min.js"></script>

@section('script')
@show

<div id="app">
  <nav-bar></nav-bar>
  @yield('component')
</div>

@yield('content')

@yield('footer')

<script type="text/javascript">
_paq = [];
var medeaUser = {isGuest: true};
var cmsLink = {!! json_encode(env('CMS', 'http://medea-cms.weopendata.com')) !!};

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

<script src="/js/main.js?{{ Config::get('app.version') }}"></script>

</body>
</html>