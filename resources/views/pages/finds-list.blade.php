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
        <a @click.prevent="sortBy('findDate')" :class="{active:filterState.order=='findDate', reverse:filterState.order=='-findDate'}">vondstdatum</a>
        <a @click.prevent="sortBy('identifier')" :class="{active:filterState.order=='identifier', reverse:filterState.order=='-identifier'}">vondstnummer (ID)</a>
      </span>
      <label class="pull-right" style="margin-left:20px">
        <button class="ui basic button" :class="{green:filterState.type=='heatmap'}" @click="mapToggle('heatmap')">Heatmap</button>
      </label>
      <label class="pull-right">
        <button class="ui basic button" :class="{green:filterState.type=='map'}" @click="mapToggle('map')">Kaart</button>
      </label>
    </div>
    <div v-if="filterState.type" id="mapview" class="card mapview" v-cloak>
      <div v-if="filterState.type=='heatmap'&&!HelpText.heatmap" class="card-help">
        <h1>Heatmap</h1>
        <p>
          Deze heatmap toont de verdeling van alle vondsten die voldoen aan de ingegeven filter.
        </p>
        <p>
          <button class="ui green button" @click="hideHelp('heatmap')">OK, niet meer tonen</button>
        </p>
      </div>
      <div v-if="filterState.type!='heatmap'&&!HelpText.map" class="card-help">
        <h1>Kaart</h1>
        <p>
          Deze kaart geeft aan waar de vondsten op deze pagina gedaan werden.
          De lijst van vondsten hieronder bevat tot 20 vondsten per pagina.
        </p>
        <p>
          <img src="/assets/img/help-area.png" height="40px"> Ruwe vondstlocatie
        </p>
        <p>
          <img src="/assets/img/help-marker.png" height="40px"> Precieze vondstlocatie (alleen bij eigen vondst)
        </p>
        <p>
          <button class="ui green button" @click="hideHelp('map')">OK, niet meer tonen</button>
        </p>
      </div>
      <google-map :center.sync="map.center" :zoom.sync="map.zoom">
        <div v-if="filterState.type=='heatmap'">
          <rectangle v-for="f in heatmap" :bounds="f.bounds" :options="f.options"></rectangle>
        </div>
        <div v-else>
          <marker v-for="f in finds | markable" @g-click="mapClick(f)" @g-mouseover="mapClick(f)" :position.sync="f.position"></marker>
          <rectangle v-for="f in finds | rectangable" @g-click="mapClick(f)" :bounds="f.bounds" :options="markerOptions"></rectangle>
          <div class="gm-panel" style="direction: ltr; overflow: hidden; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;right: 10px;" v-if="map.info" v-html="map.info"></div>
        </div>
        <div class="gm-panel" style="direction: ltr; overflow: hidden; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;top:auto;left: 10px;bottom: 10px;" @click="showHelp(filterState.type=='heatmap'?'heatmap':'map')">Help</div>
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