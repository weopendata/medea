@extends('main')

@section('title', 'Vondsten')

@section('showmap')
<a href="/finds" class="item" :class="{active:!filterState.showmap}" @click.prevent="mapshow(false)">Vondsten</a>
<a href="/finds?showmap=true" class="item" :class="{active:filterState.showmap}" @click.prevent="mapshow(true)">Kaart</a>
@endsection

@section('content')
<div class="ui container">
  <div class="list-left">
    <finds-filter :name.sync="filterName" :model.sync="filterState" :saved="saved"></finds-filter>
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
    </div>
    <div v-if="filterState.showmap" id="mapview" class="card mapview" v-cloak>
      <map :center.sync="map.center" :zoom.sync="map.zoom">
        <marker v-for="f in finds | markable" @g-click="mapClick(f)" v-if="markerNeeded||f.accuracy==1" :position.sync="f.position"></marker>
        <rectangle v-for="f in finds | markable" @g-click="mapClick(f)" :bounds="f.bounds" :options="markerOptions"></circle>
        <div class="gm-panel" style="direction: ltr; overflow: hidden; text-align: center; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;right: 10px;" v-if="map.info" v-html="map.info"></div>
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