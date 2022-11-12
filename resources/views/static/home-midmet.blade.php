@extends('main')

@section('title', 'Home')

@section('nav')
  <div class="nav-home">
    @endsection

    @section('component')
      <home-mid-met></home-mid-met>
    @endsection

    <script type="text/javascript">
      var stats = {!! json_encode($stats) !!}
      var cmsLink = {!! json_encode(env('CMS_LINK')) !!}
    </script>