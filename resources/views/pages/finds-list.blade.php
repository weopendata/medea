@extends('main')

@section('title', 'Vondsten')

@section('content')
<div class="ui container" :class="{fetching:fetching}">
  <div class="list-left">
    <finds-filter :name.sync="filterName" :model.sync="filterState" :saved="saved"></finds-filter>
  </div>
  <div class="list-right">
    <div class="list-controls">
      <span class="finds-order">
        Sorteren op:
        <a @click.prevent="sortBy('findDate')" :class="{active:filterState.order=='findDate', reverse:filterState.order=='-findDate'}">datum</a>
        <a @click.prevent="sortBy('identifier')" :class="{active:filterState.order=='identifier', reverse:filterState.order=='-identifier'}">vondstnummer</a>
      </span>
      <label class="pull-right" style="margin-left:20px">
        <button class="ui basic button" :class="{green:filterState.type=='heatmap'}" @click="mapToggle('heatmap')">Heatmap</button>
      </label>
      <label class="pull-right">
        <button class="ui basic button" :class="{green:filterState.type=='map'}" @click="mapToggle('map')">Kaart</button>
      </label>
    </div>
    <div v-if="filterState.type" id="mapview" class="card mapview" v-cloak>
      <google-map :center.sync="map.center" :zoom.sync="map.zoom">
        <div v-if="filterState.type=='heatmap'">
          <rectangle v-for="f in heatmap" :bounds="f.bounds" :options="f.options"></rectangle>
        </div>
        <div v-else>
          <marker v-for="f in finds | markable" @g-click="mapClick(f)" :position.sync="f.position"></marker>
          <rectangle v-for="f in finds | rectangable" @g-click="mapClick(f)" :bounds="f.bounds" :options="markerOptions"></rectangle>
          <div class="gm-panel" style="direction: ltr; overflow: hidden; text-align: center; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;right: 10px;" v-if="map.info" v-html="map.info"></div>
        </div>
      </google-map>
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