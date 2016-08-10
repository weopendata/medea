<template>
  <div class="ui form" @change="$root.fetch()">
    <div class="field">
      <form class="ui action input" @submit.prevent="$root.fetch()">
        <input type="text" v-model="model.query" placeholder="Zoeken..." style="width:100px">
        <button class="ui icon button" :class="{green:model.query}">
          <i class="search icon"></i>
        </button>
      </form>
    </div>
    
    <h3 class="facet-title">Snelle filters</h3>
    <a href="#" class="facet-a" :class="{active:model.status=='in bewerking'}" @click.prevent="toggle('status', 'in bewerking')" v-if="$root.user.validator">Te valideren vondsten</a>
    <a href="#" class="facet-a" :class="{active:model.myfinds}" @click.prevent="toggleMyfinds">Mijn vondsten</a>
    <a href="#" class="facet-a" @click.prevent="restore(filter)" v-for="filter in saved" v-text="filter.name"></a>

    <div v-if="$root.user.validator">
      <span></span>
      <h3 class="facet-title">Validatie status</h3>
      <div class="facet-options">
        <a href="#" class="facet-a" :class="{active:model.status==opt}" @click.prevent="toggle('status', opt)" v-for="opt in ['in bewerking', 'gevalideerd', 'revisie nodig', 'embargo', 'afgekeurd']" v-text="opt"></a>
      </div>
    </div>

    <h3 class="facet-title">Categorie</h3>
    <div class="facet-options">
      <a href="#" class="facet-a" :class="{active:model.category==opt}" @click.prevent="toggle('category', opt)" v-for="opt in fields.object.category" v-text="opt"></a>
    </div>

    <h3 class="facet-title">Periode</h3>
    <div class="facet-options">
      <a href="#" class="facet-a" :class="{active:model.period==opt}" @click.prevent="toggle('period', opt)" v-for="opt in fields.classification.period" v-text="opt"></a>
    </div>

    <h3 class="facet-title">Materiaal</h3>
    <div class="facet-options">
      <a href="#" class="facet-a" :class="{active:model.objectMaterial==opt}" @click.prevent="toggle('objectMaterial', opt)" v-for="opt in fields.object.objectMaterial" v-text="opt"></a>
    </div>

    <h3 class="facet-title">Techniek</h3>
    <div class="facet-options">
      <a href="#" class="facet-a" :class="{active:model.technique==opt}" @click.prevent="toggle('technique', opt)" v-for="opt in fields.object.technique" v-text="opt"></a>
    </div>
  </div>
</template>

<script>
import FindEvent from './FindEvent';

export default {
  props: ['model', 'saved'],
  data () {
    return {
      fields: window.fields,
      advanced: false
    }
  },
  methods: {
    restore (filter) {
      console.warn('Restoring save filter:', filter)
      this.model = filter
      this.$root.fetch()
    },
    unset (filter) {
      this.model[filter] = null
      this.$root.fetch()
    },
    toggle (filter, value) {
      if (!value) {
        this.model[filter] = !this.model[filter]
      } else {
        this.model[filter] = this.model[filter] == value ? false : value
      }
      this.$root.fetch()
    },
    toggleMyfinds () {
      this.model.myfinds = this.model.myfinds ? false : 'yes';
      this.$root.fetch()
    },
    sortBy (type) {
      if (this.model.order == type) {
        this.model.order = '-' + type
      } else if (this.model.order == '-' + type) {
        this.model.order = false
      } else {
        this.model.order = type
      }
      this.$root.fetch()
    }
  },
  ready () {
    $('.ui.dropdown').dropdown()
  },
  watch: {
    advanced () {
      $('select.ui.dropdown').dropdown()
    }
  },
  components: {
    FindEvent
  }
}
</script>
