@extends('main')

@section('title', 'Vondsten')

@section('showmap')
<a href="/finds" class="item" :class="{active:!filterState.showmap}" @click.prevent="mapshow(false)">Vondsten</a>
<a href="/finds?showmap=true" class="item" :class="{active:filterState.showmap}" @click.prevent="mapshow(true)">Kaart</a>
@endsection

@section('content')
<div class="ui container">
  <div class="list-left">
    <finds-filter :model.sync="filterState" :saved="[{name:'Valideren', status: 'in bewerking'}, {name:'ijzer only', objectMaterial: 'ijzer'}, {name:'All my finds', myfinds: true}]" ref="filter"></finds-filter>
  </div>
  <div class="list-right">
    <div class="card-bar">
      <span class="finds-order">
        Sorteren op:
        <a @click.prevent="sortBy('findDate')" :class="{active:filterState.order=='findDate', reverse:filterState.order=='-findDate'}">datum</a>
        <a @click.prevent="sortBy('identifier')" :class="{active:filterState.order=='identifier', reverse:filterState.order=='-identifier'}">vondstnummer</a>
      </span>
      <label class="pull-right">
        <input type="checkbox" v-model="filterState.showmap"> Kaart
      </label>
      <label class="pull-right" v-if="!$root.user.isGuest">
        <input type="checkbox" :checked="filterState.myfinds" @change="toggleMyfinds"> Alleen mijn vondsten
      </label>
    </div>
    <div v-if="filterState.showmap" class="card mapview">
      <map :center.sync="map.center" :zoom.sync="map.zoom">
        <marker v-for="f in finds | markable" :position.sync="f.position"></marker>
        <circle v-for="f in finds | markable" :center.sync="f.position" :radius.sync="f.accuracy" :options="markerOptions"></circle>
      </map>
      <map-controls :showmap.sync="filterState.showmap"></map-controls>
    </div> 
    <finds-list :finds="finds" :user="user" :paging="paging"></finds-list>
  </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
window.initialFinds = {!! json_encode($finds) !!};
window.filterState = {!! json_encode($filterState) !!};
window.fields = {!! json_encode($fields) !!};
window.link = {!! json_encode($link) !!};
</script>
<script src="{{ asset('js/finds-list.js') }}"></script>
@endsection