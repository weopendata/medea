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
    </script>

    <script>
      import HomeMidMet from '../../assets/js/components/HomeMidMet.vue'

      export default {
        components: { HomeMidMet }
      }
    </script>
