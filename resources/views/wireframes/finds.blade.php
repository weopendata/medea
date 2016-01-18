@extends('main')

@section('content')
<script type="text/x-template" id="lookup-template">
  <table class="table">
    <thead>
      <tr>
        <th v-for="column in columns"
          @click="sortBy(column.objectKey)"
          :class="{active: sortKey == column.objectKey}">
          @{{column.displayName | capitalize}}
          <span class="arrow"
            :class="sortOrders[column.objectKey] > 0 ? 'asc' : 'dsc'">
          </span>
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="
        entry in data
        | filterBy filterKey
        | orderBy sortKey sortOrders[sortKey]
        ">
        <td v-for="column in columns">
          @{{entry[column.objectKey]}}
        </td>
      </tr>
    </tbody>
  </table>
</script>

<div id="app" class="container">
    <div class="row">
    <form id="search" @submit.prevent="">
            <div class="input-group col-md-2 form-group pull-right">
              <input type="text" class="form-control" v-model="searchQuery">
              <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search"></span></button>
              </span>
            </div>
    </form>
    </div>

    <div class="row">
        <lookup
            :data="gridData"
            :columns="gridColumns"
            :filter-key="searchQuery">
          </lookup>
    </div>

    <div class="row">
      <a href="finds/new" class="btn btn-success pull-right"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Voeg toe</a>
    </div>
</div>
@stop

@section('script')
<script>
    // register the grid component
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
    })
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
})

// bootstrap the app
var app = new Vue({
  el: '#app',
  data: {
    searchQuery: '',
    gridColumns: [
            {'displayName' : 'titel', 'objectKey' : 'title'},
            {'displayName' : 'categorie', 'objectKey' : 'category'},
            {'displayName' : 'beschrijving', 'objectKey' : 'description'},
            {'displayName' : 'dimensie', 'objectKey' : 'dimension'},
            {'displayName' : 'periode', 'objectKey' : 'date'}
            ],
    gridData: [
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                date : "20 n. Chr. - 100 n. Chr."
            },
            {
                title : "Centurion gesp",
                category : "gesp",
                description : "Een riem die aan een centurion toebehoorde.",
                dimension : "100x5 cm",
                date : "0 n. Chr. - 50 n. Chr."
            },
            {
                title : "Speer uit de Griekse periode",
                category : "speer",
                description : "Een typisch griekse speer uit de oudheid.",
                dimension : "200x5x8 cm",
                date : "200 v. Chr. - 150 n. Chr."
            }
    ]
  }
});
</script>
@stop