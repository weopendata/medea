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
      <a href="#" @click.prevent="restore(filter)" v-for="filter in saved" v-text="filter.name" class="facet-a"></a>
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.category">
          <option value="*">Alle categorieën</option>
          <option v-for="opt in fields.object.category" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.period">
          <option value="*">Alle perioden</option>
          <option v-for="opt in fields.classification.period" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.objectMaterial">
          <option value="*">Alle materialen</option>
          <option v-for="opt in fields.object.objectMaterial" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.technique">
          <option value="*">Alle technieken</option>
          <option v-for="opt in fields.object.technique" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field" v-if="$root.user.validator">
        <select class="ui search fluid dropdown category" v-model="model.status">
          <option value="in bewerking">in bewerking</option>
          <option value="gevalideerd">gevalideerd</option>
          <option value="revisie nodig">revisie nodig</option>
          <option value="onder embargo" v-if="$root.user.administrator">embargo</option>
          <option value="verwijderd" v-if="$root.user.administrator">afgekeurd</option>
        </select>
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
