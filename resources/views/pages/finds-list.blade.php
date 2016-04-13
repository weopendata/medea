@extends('main')

@section('title', 'Vondsten')

@section('content')
<div v-if="!showmap" transition="fromleft">
  <div class="ui container">
    <finds-filter :model.sync="filterState"></finds-filter>
    <finds-list :finds="finds" :user="user"></finds-list>
    <div v-if="!finds.length&&!user.isGuest" class="finds-empty">
      <h1>
        Geen resultaten
        <br><small>Er zijn geen vondsten die voldoen aan de criteria</small>
      </h1>
    </div>
    <div class="finds-cta">
      <p>
        Blijf op de hoogte van vondsten met deze criteria:
      </p>
      <p>
        <a href="" class="ui green button" :class="{big:!finds.length&&!user.isGuest}">Zoekopdracht bewaren</a>
      </p>
    </div>
    <div class="finds-cta">
      <p>
        Zelf iets gevonden?
      </p>
      <p>
        <a href="{{url('/finds/create')}}" class="ui green button" :class="{big:!finds.length&&!user.isGuest}">Vondst toevoegen</a>
      </p>
    </div>
  </div>
</div>
<div v-if="showmap" transition="fromright">
  <map :center.sync="map.center" :zoom.sync="map.zoom" style="display:block;height:100vh;">
    <marker v-for="f in finds | markable" :position.sync="f.position"></marker>
    <circle v-for="f in finds | markable" :center.sync="f.position" :radius="f.accuracy" :options="markerOptions"></circle>
  </map>
  <div class="map-controls">
    <div class="map-modal">
      <div class="field">
        <div class="ui right labeled fluid input">
          <input type="text" v-model="search" placeholder="zoeken">
          <button class="ui icon button label"><i class="search icon"></i></button>
        </div>
      </div>
      this is somethin
    </div>
    <a class="ui black button" @click.prevent="showmap=false">Terug naar lijst</a>
  </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
window.initialFinds = {!! json_encode($finds) !!};
window.filterState = {!! json_encode($filterState) !!};
window.fields = {!! json_encode($fields) !!};
</script>
<script src="{{ asset('js/finds-list.js') }}"></script>
@endsection