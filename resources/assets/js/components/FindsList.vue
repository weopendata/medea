<template>
  <div class="ui very relaxed items">
    <find-event v-for="f in finds" :find="f" :user="user"></find-event>
    <div class="paging">
      <a v-if="paging.previous" :href="paging.previous.url||'#'" class="ui blue button">Vorige</a>
      <a v-if="paging.next" :href="paging.next.url||'#'" class="ui blue button">Volgende</a>
      <a v-if="paging.last" :href="paging.last.url||'#'" class="ui blue button">Laatste</a>
    </div>
    <div v-if="!finds.length" class="finds-empty">
      <h1>
        Geen resultaten
        <br><small>Er zijn geen vondsten die voldoen aan de criteria</small>
      </h1>
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
import FindEvent from './FindEvent';

export default {
  props: ['user', 'finds', 'paging'],
  data () {
    return {
      favName: '',
      showFavName: false
    }
  },
  computed: {
    exists () {
      return this.$root.saved.filter(s => s.name === this.$root.filterName).length
    }
  },
  methods: {
    toggleFav () {
      if (this.exists) {
        this.$root.$emit('rmSearch')
      } else if (!this.showFavName) {
        this.showFavName = true
      } else if (this.favName) {
        this.$root.$emit('saveSearch', this.favName)
        this.favName = ''
        this.showFavName = false
      }
    }
  },
  components: {
    FindEvent
  }
}
</script>