@extends('main')

@section('title', 'Vondsten')

@section('showmap')
<a href="/finds" class="item" :class="{active:!filterState.showmap}" @click.prevent="mapshow(false)">Vondsten</a>
<a href="/finds?showmap=true" class="item" :class="{active:filterState.showmap}" @click.prevent="mapshow(true)">Kaart</a>
@endsection

@section('content')
<div v-if="!filterState.showmap" transition="fromleft">
  <div class="ui container">
    <finds-filter :model.sync="filterState" :saved="[{name:'ijzer only', material: 'ijzer'}, {name:'All my finds', myfinds: true}]"></finds-filter>
    <finds-list :finds="finds | filterBy relevant" :user="user"></finds-list>
  </div>
</div>
<div v-else transition="fromright">
  <map :center.sync="map.center" :zoom.sync="map.zoom" style="display:block;height:100vh;">
    <marker v-for="f in finds | markable" :position.sync="f.position"></marker>
    <circle v-for="f in finds | markable" :center.sync="f.position" :radius="f.accuracy" :options="markerOptions"></circle>
  </map>
  <map-controls :showmap.sync="filterState.showmap"></map-controls>
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