@extends('main')

@section('title', 'Home')

@section('nav')
<div class="nav-home">
@endsection

@section('component')
<home></home>
@endsection

@section('footer')
<br style="clear:both;">
<footer style="position: unset !important;">
  <div class="ui container">
    <div style="float:right">
      <a target="_blank" href="https://blog.vondsten.be/over-medea/contact">Contact</a> &nbsp; &middot; &nbsp;
      <a href="/feedback">Feedback</a>
    </div>
    <p>
      <a href="https://blog.vondsten.be/gebruikersvoorwaarden">Gebruikersvoorwaarden</a>
    </p>
    <p>
      <a href="https://creativecommons.org/licenses/by-nc/4.0/">
        <img src="https://licensebuttons.net/l/by-nc/4.0/80x15.png" style="vertical-align:middle">
        <span style="opacity:.5;font-size:12px">We gebruiken een open content licentie</span>
      </a>
    </p>
  </div>
</footer>
@endsection

<script type="text/javascript">
var stats = {!! json_encode($stats) !!}
</script>

