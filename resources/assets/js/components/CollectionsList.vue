<template>
  <div class="ui very relaxed items">
    <div class="list-controls">
      <span class="finds-order">
        Sorteren op:
        <a @click.prevent="sortBy('title')" :class="sortClass('title')">Titel</a>
        <a @click.prevent="sortBy('created_at')" :class="sortClass('created_at')">Datum aangemaakt</a>
      </span>
      <label class="pull-right">
        <a href="/collections/create" class="ui green button">Collectie toevoegen</a>
      </label>
    </div>
    <br>
    <collection v-for="collection in collections" :collection="collection"></collection>
    <div v-if="!collections.length" class="finds-empty">
      <h1>
        Er zijn nog geen collecties gemaakt.
        <br>
      </h1>
    </div>
    <div v-if="paging" class="paging">
      <div class="paging-current">
        Pagina {{ currentPage }} van {{ totalPages }}
      </div>
      <a :href="paging.first.url" v-if="paging.first" class="ui blue icon button"><i class="double angle left icon"></i></a>
      <a :href="paging.previous.url" v-if="paging.previous" class="ui blue button">Vorige</a>
      <a :href="paging.last.url" v-if="paging.last" class="ui blue icon button pull-right"><i class="double angle right icon"></i></a>
      <a :href="paging.next.url" v-if="paging.next" class="ui blue button pull-right">Volgende</a>
    </div>
  </div>
</template>
<script>
  import { incomingCollection, inert, fromQuery } from '../const.js'
  import Collection from '../components/Collection.vue'
  import parseLinkHeader from 'parse-link-header'

  export default {
    data () {
      return {
        paging: parseLinkHeader(window.link),
        collections: (window.initialCollections || []).map(incomingCollection),
        filterState: window.filterState || {}
      }
    },
    methods: {
      sortBy(property){
        let sortBy = (this.filterState.sortOrder === 'ASC' && this.filterState.sortBy === property ) ? 'DESC' : 'ASC'
        window.location = '?sortBy=' + property + '&sortOrder=' + sortBy
      },
      sortClass(property){
        if(property === this.filterState.sortBy){
          return this.filterState.sortOrder === 'ASC' ? 'active' : 'reverse'
        }
      }
    },
    computed: {
      currentPage () {
        if (this.paging.next) {
          return this.paging.next.offset / this.paging.next.limit
        }
        if (this.paging.previous) {
          return 2 + this.paging.previous.offset / this.paging.previous.limit
        }
        return 1
      },
      totalPages () {
        if (this.paging.last) {
          return 1 + this.paging.last.offset / this.paging.last.limit
        }
        return this.currentPage
      }
    },
    ready () {
    },
    components: {
      Collection,
    }
  }
</script>