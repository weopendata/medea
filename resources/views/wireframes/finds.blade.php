@extends('main')

@section('content')
<script type="text/x-template" id="lookup-template">
  <table class="table">
    <thead>
      <tr>
        <th v-for="column in columns"
        @click="sortBy(column.objectKey)">
        @{{column.displayName | capitalize}}
        <span class="arrow"
        :class="sortOrders[column.objectKey] > 0 ? 'asc' : 'dsc'">
      </span>
    </th>
  </tr>
</thead>
<tbody>
  <tr v-for="entry in data">
  <td v-for="column in columns">
    @{{entry[column.objectKey]}}
  </td>
</tr>
</tbody>
</table>
</script>

<div id="app" class="container">
    <div id="experts" v-if="viewRole == 'Publiek'">
      <div class="row row-stretch tall-col">
        <div class="col-md-3">
          <div class="row">
            <form id="search" @submit.prevent="">
              <div class="input-group col-md-12 form-group pull-left">
                <input type="text" class="form-control" v-model="searchQuery">
                <span class="input-group-btn">
                  <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search"></span></button>
                </span>
              </div>
            </form>
          </div>

          <div class="row lookup-component">
            <lookup
            :data="gridData"
            :columns="gridColumns"
            :filter-key="searchQuery">
            </lookup>
          </div> <!-- component div -->
        </div> <!-- col-md-3 -->

        <div class="col-md-9">
          <div id="map">

          </div>
        </div>
      </div><!-- main row -->
    </div>

    <div id="experts" v-if="viewRole == 'Detectorist'">
      <div class="row row-stretch tall-col">
        <div class="col-md-3">
          <div class="row">
            <form id="search" @submit.prevent="">
              <div class="input-group col-md-12 form-group pull-left">
                <select v-model="detectoristEntities" class="form-control">
                    <option value="inReview" selected>In review</option>
                    <option value="embargo">Gepubliceerd</option>
                    <option value="published">Onder embargo</option>
                    <option value="deleted">Verwijderd door admin</option>
                </select>
              </div>
            </form>
          </div>

          <div class="row lookup-component">
            <lookup
            :data="detectoristData"
            :columns="gridColumns"
            :filter-key="searchQuery">
            </lookup>
          </div> <!-- component div -->
        </div> <!-- col-md-3 -->

        <div class="col-md-9">
          <div id="map">

          </div>
        </div>
      </div><!-- main row -->
    </div>

    <div id="experts" v-if="viewRole == 'Vondstexpert' || viewRole == 'Onderzoeker'">
      <div class="row row-stretch tall-col">
        <div class="col-md-12">
          <div class="row">
            <form id="search" @submit.prevent="">
              <div class="input-group col-md-3 form-group pull-left">
                <input type="text" class="form-control" v-model="searchQuery">
                <span class="input-group-btn">
                  <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search"></span></button>
                </span>
              </div>
              <div class="col-md-6">
                <p class="help-block"><span class="glyphicon glyphicon-exclamation-sign help"></span> Vondsten werden aangepast aan uw expertisedomein</p>
              </div>
            </form>
          </div>

          <div class="row lookup-component">
            <lookup
            :data="gridData"
            :columns="gridColumnsAll"
            :filter-key="searchQuery">
            </lookup>
          </div> <!-- component div -->
        </div> <!-- col-md-3 -->
      </div><!-- main row -->
    </div>
</div>
@stop

@section('script')
<script>
  Vue.component('lookup', {
    template: '#lookup-template',
    props: {
      data: Array,
      columns: Array,
      filterKey: String
    },
    data: function () {
      var sortOrders = {}
      this.columns.forEach(function (key) {
        sortOrders[key.objectKey] = 1
      });

      return {
        sortKey: '',
        sortOrders: sortOrders
      }
    },
    methods: {
      sortBy: function (key) {
        this.sortKey = key
        this.sortOrders[key] = this.sortOrders[key] * -1
      },
    }

  });

// bootstrap the app
var vm = new Vue({
  el: '#app',

  ready : function () {
    this.initMap(this.gridData);
  },

  data: {
    searchQuery: '',

    detectoristEntities : '',

    viewRole : 'Publiek',

    gridColumns: [
      {'displayName' : 'titel', 'objectKey' : 'title'}
    ],

    gridColumnsAll: [
      {'displayName' : 'titel', 'objectKey' : 'title'},
      {'displayName' : 'beschrijving', 'objectKey' : 'description'},
      {'displayName' : 'categorie', 'objectKey' : 'category'},
      {'displayName' : 'datum', 'objectKey' : 'date'}
    ],

    origData: [
    {
      title : "Gouden Romeinse munt",
      category : "munt",
      description : "Een gouden munt uit de vroege romeinse tijd.",
      dimension : "5x5 cm",
      date : "20 n. Chr. - 100 n. Chr.",
      location : [50.806905897875, 3.3014484298127]
    },
    {
      title : "Centurion gesp",
      category : "gesp",
      description : "Een riem die aan een centurion toebehoorde.",
      dimension : "100x5 cm",
      date : "0 n. Chr. - 50 n. Chr.",
      location : [50.806905897875, 3.2227863148763]
    },
    {
      title : "Speer uit de Griekse periode",
      category : "speer",
      description : "Een typisch griekse speer uit de oudheid.",
      dimension : "200x5x8 cm",
      date : "200 v. Chr. - 150 n. Chr.",
      location : [50.806905897875, 3.2474169937074]
    }
    ],

    gridData : [
    {
      title : "Gouden Romeinse munt",
      category : "munt",
      description : "Een gouden munt uit de vroege romeinse tijd.",
      dimension : "5x5 cm",
      date : "20 n. Chr. - 100 n. Chr.",
      location : [50.806905897875, 3.3014484298127]
    },
    {
      title : "Centurion gesp",
      category : "gesp",
      description : "Een riem die aan een centurion toebehoorde.",
      dimension : "100x5 cm",
      date : "0 n. Chr. - 50 n. Chr.",
      location : [50.806905897875, 3.2227863148763]
    },
    {
      title : "Speer uit de Griekse periode",
      category : "speer",
      description : "Een typisch griekse speer uit de oudheid.",
      dimension : "200x5x8 cm",
      date : "200 v. Chr. - 150 n. Chr.",
      location : [50.806905897875, 3.2474169937074]
    }
    ],

    detectoristData : [
    {
        title : "Centurion gesp",
        category : "gesp",
        description : "Een riem die aan een centurion toebehoorde.",
        dimension : "100x5 cm",
        date : "0 n. Chr. - 50 n. Chr.",
        location : [50.806905897875, 3.2227863148763]
    }
    ],

    detectoristDataList : {
        'inReview' : [
            {
                title : "Centurion gesp",
                category : "gesp",
                description : "Een riem die aan een centurion toebehoorde.",
                dimension : "100x5 cm",
                date : "0 n. Chr. - 50 n. Chr.",
                location : [50.806905897875, 3.2227863148763]
            }
        ],

        'published' : [
            {
                title : "Speer uit de Griekse periode",
                category : "speer",
                description : "Een typisch griekse speer uit de oudheid.",
                dimension : "200x5x8 cm",
                date : "200 v. Chr. - 150 n. Chr.",
                location : [50.806905897875, 3.2474169937074]
            }
        ],

        'embargo' : [
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                date : "20 n. Chr. - 100 n. Chr.",
                location : [50.806905897875, 3.3014484298127]
            }
        ],

        'deleted' : [
            {
                title : "Gothische kathedraal",
                category : "Gebouw",
                description : "Een gotische kathedraal, op het kerkplein.",
                dimension : "10000x10000 cm",
                date : "1300 n. Chr. - 1450 n. Chr.",
                location : [50.806905897875, 3.2227863148763]
            }
        ]
    }
  },

  methods : {
    initMap : function (data) {
        window.map = L.map('map').setView([50.806905897875, 3.3014484298127], 10);

        var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        var osmAttrib='Map data © <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
        layer = L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          maxZoom: 19,
          attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
      });


        layer.addTo(map);

        window.redMarker = L.ExtraMarkers.icon({
          icon: 'fa-eye',
          markerColor: 'red',
          shape: 'square',
          prefix: 'fa'
      });

        layergroup = [];

        for (i = 0; i < data.length; ++i) {
            var item = data[i];
          var marker = L.marker(item.location, {icon: redMarker,});
          marker.bindPopup("<b>" + item.title + "</b><br><b>category: " + item.category + "</b><br>" + item.description).openPopup();
          layergroup.push(marker);
      }

      window.markersLayer = L.layerGroup(layergroup).addTo(window.map);
    }
  },

  watch : {
    detectoristEntities : function (val) {
        this.detectoristData = this.detectoristDataList[val];

        window.markersLayer.clearLayers();

        layergroup = [];

        for (i = 0; i < this.detectoristData.length; ++i) {
            var item = this.detectoristData[i];
            var marker = L.marker(this.detectoristData[i].location, {icon: window.redMarker,});
            marker.bindPopup("<b>" + item.title + "</b><br><b>category: " + item.category + "</b><br>" + item.description).openPopup();
            layergroup.push(marker);
        }

        window.markersLayer = L.layerGroup(layergroup).addTo(window.map);
    },

    viewRole : function (val) {
        if (val == 'Detectorist') {
            this.initMap(this.detectoristData);
        } else if (val == 'Publiek') {
            this.initMap(this.origData);
        } else {
            $('[data-toggle="popover"]').popover({
                placement : 'top'
            });
        }
    },

    searchQuery : function (val, oldVal) {
      window.markersLayer.clearLayers();

      var items = this.origData;
      var filtered = [];

      if (val) {
        items.forEach(function (el) {
          if (
            el.title.indexOf(val) > -1 ||
            el.category.indexOf(val) > -1 ||
            el.description.indexOf(val) > -1)
          {
            filtered.push(el);
          }
        });
      } else {
        filtered = this.origData;
      }

      this.gridData = filtered;

      layergroup = [];

      for (i = 0; i < filtered.length; ++i) {
        var item = filtered[i];
        var marker = L.marker(filtered[i].location, {icon: window.redMarker,});
        marker.bindPopup("<b>" + item.title + "</b><br><b>category: " + item.category + "</b><br>" + item.description).openPopup();
        layergroup.push(marker);
      }

      window.markersLayer = L.layerGroup(layergroup).addTo(window.map);
    }
  }
});
</script>
@stop