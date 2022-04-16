<template>
  <div>
    <div class="find-results__container">
      <div class="find-results__loading" v-if="fetching">
        <div style="margin: auto; font-size: 1.5rem;">Bezig met zoeken...</div>
      </div>
      <div v-else-if="finds.length">
        <template v-if="cardStyle == 'tile'">
          <find-event-small v-for="(find, index) in finds" :key="'find_result_' + index" :find="find"/>
        </template>
        <template v-else>
          <find-event v-for="(find, index) in finds" :key="'find_result_' + index" :find="find" :user="user"/>
        </template>
      </div>
      <div v-else-if="!finds.length" class="finds-empty">
        <h1>
          Geen resultaten
          <br><small>Er zijn geen vondsten die voldoen aan de criteria</small>
        </h1>
      </div>
    </div>
    <div v-if="finds.length && !fetching" class="paging">
      <div class="paging-current">
        Pagina {{ currentPage }} van {{ totalPages }}
      </div>
      <button v-if="paging.previous" @click="to({offset:0})" class="ui blue icon button"><i
          class="double angle left icon"></i></button>
      <button v-if="paging.previous" @click="to(paging.previous)" class="ui blue button">Vorige</button>
      <button v-if="paging.next" @click="to(paging.last||paging.next)" class="ui blue icon button pull-right"><i
          class="double angle right icon"></i></button>
      <button v-if="paging.next" @click="to(paging.next)" class="ui blue button pull-right">Volgende</button>
    </div>
    <div class="ui form finds-cta">
      <div class="field" v-if="showFavName">
        <label>Geef deze zoekopdracht een naam</label>
        <input type="text" v-model="favName" style="width: 200px">
      </div>
      <p v-if="!user.isGuest">
        <button type="button" class="ui large button" :class="{green:favName}" @click.prevent="toggleFav"
                :disabled="showFavName&&!favName"><i class="ui alarm icon"></i> Zoekopdracht
          {{ exists ? 'verwijderen uit' : 'toevoegen aan' }} favorieten
        </button>
      </p>
    </div>
  </div>
</template>

<script>
import FindEvent from './FindEvent'
import FindEventSmall from './FindEventSmall.vue'

export default {
  props: ['user', 'finds', 'fetching', 'paging', 'saved', 'cardStyle'],
  data () {
    return {
      favName: '',
      showFavName: false
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
    },
    exists () {
      return this.saved.filter(s => s.name === this.filterName).length
    }
  },
  methods: {
    to (q) {
      this.$emit('filtersChanged', { offset: q.offset })
    },
    toggleFav () {
      if (this.exists) {
        this.$emit('rmSearch')
      } else if (!this.showFavName) {
        this.showFavName = true
      } else if (this.favName) {
        this.$emit('saveSearch', this.favName)
        this.favName = ''
        this.showFavName = false
      }
    }
  },
  components: {
    FindEvent,
    FindEventSmall
  }
}
</script>
