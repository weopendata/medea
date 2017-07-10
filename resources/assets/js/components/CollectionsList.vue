<template>
  <div class="ui very relaxed items">
    <div class="create-cta">
      <label class="pull-right">
        <a :href="createUri" class="ui green button">Collectie toevoegen</a>
      </label>
    </div>
      <collection v-for="collection in collections" :collection="collection"></collection>

    <div v-if="!collections.length" class="finds-empty">
      <h1>
        Geen resultaten
        <br><small>Er zijn geen vondsten die voldoen aan de criteria</small>
      </h1>
    </div>
    <div v-else class="paging">
      <div class="paging-current">
        Pagina {{ currentPage }} van {{ totalPages }}
      </div>
      <button v-if="paging.previous" @click="to({offset:0})" class="ui blue icon button"><i class="double angle left icon"></i></button>
      <button v-if="paging.previous" @click="to(paging.previous)" class="ui blue button">Vorige</button>
      <button v-if="paging.next" @click="to(paging.last||paging.next)" class="ui blue icon button pull-right"><i class="double angle right icon"></i></button>
      <button v-if="paging.next" @click="to(paging.next)" class="ui blue button pull-right">Volgende</button>
    </div>
    <div class="ui form finds-cta">
      <div class="field" v-if="showFavName">
        <label>Geef deze zoekopdracht een naam</label>
        <input type="text" v-model="favName" style="width: 200px">
      </div>
      <p v-if="!user.isGuest">
        <button type="button" class="ui large button" :class="{green:favName}" @click.prevent="toggleFav" :disabled="showFavName&&!favName"><i class="ui alarm icon"></i> Zoekopdracht {{ exists ? 'verwijderen uit' : 'toevoegen aan' }} favorieten</button>
      </p>
    </div>

  </div>
</template>
<script>
  import {inert, fromQuery} from '../const.js'
  import Collection from '../components/Collection.vue'

  export default {
    props: ['collections', 'paging'],
    data () {
      return {}
    },
    computed: {
      createUri () {
        return '/collections/create'
      },
      currentPage () {
        if (this.paging.next) {
          return this.paging.next.offset /  this.paging.next.limit
        }
        if (this.paging.previous) {
          return 2 + this.paging.previous.offset /  this.paging.previous.limit
        }
        return 1
      },
      totalPages () {
        if (this.paging.last) {
          return 1 + this.paging.last.offset /  this.paging.last.limit
        }
        return this.currentPage
      },
    },
    methods: {},
    components: {
      Collection
    }
  }
</script>
<style>
  .create-cta{
    position: relative;
    overflow: auto;
    max-width: 900px;
    margin: 0 auto 2em;
  }
</style>