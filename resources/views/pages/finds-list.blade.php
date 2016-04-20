@extends('main')

@section('title', 'Vondsten')

@section('content')
<div v-if="!showmap" transition="fromleft">
  <div class="ui container">
    <finds-filter :model.sync="filterState" :saved="[{name:'Some filter'}, {name:'Other filter'}]"></finds-filter>
    <finds-list :finds="finds | filterBy relevant" :user="user"></finds-list>
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
<dev-bar :user="user"></dev-bar>
@endsection

@section('script')
<script type="text/javascript">
window.initialFinds = {!! json_encode($finds) !!};
window.filterState = {!! json_encode($filterState) !!};
window.fields = {!! json_encode($fields) !!};
</script>
<script src="{{ asset('js/finds-list.js') }}"></script>
@endsection