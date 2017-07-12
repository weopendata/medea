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

    <div class="facets">
      <div v-if="$root.user.email" class="facet">
        <h3 class="facet-title"><i class="ui star icon"></i> Favorieten</h3>
        <a href="#" class="facet-a" :class="{active:name=='$val'}" @click.prevent="restore({name:'$val', state:{status:'Klaar voor validatie'}})" v-if="$root.user.validator">Te valideren vondsten</a>
        <a href="#" class="facet-a" :class="{active:model.myfinds}" @click.prevent="toggle('myfinds', true)">Mijn vondsten</a>
        <a href="#" class="facet-a" :class="{active:name==fav.name}" @click.prevent="restore(fav)" v-for="fav in saved" v-text="fav.name"></a>
      </div>

      <facet label="Validatie status" prop="status" :options="statusOptions"></facet>
      <facet label="Embargo" prop="embargo" :options="embargoOptions"></facet>
      <facet label="Periode" prop="period" :options="fields.classification.period"></facet>
      <facet label="Materiaal" prop="objectMaterial" :options="fields.object.objectMaterial"></facet>
      <facet label="Techniek" prop="technique" :options="fields.object.technique"></facet>
      <facet label="Oppervlaktebehandeling" prop="modification" :options="modificationFields"></facet>
      <facet label="Categorie" prop="category" :options="fields.object.category"></facet>
      <facet label="Collecties" prop="collection" :options="fields.collections"></facet>
    </div>
  </div>
</template>

<script>
import ls from 'local-storage'

import FindEvent from './FindEvent.vue';
import Facet from './Facet.vue';
import {inert} from '../const.js';

var backupState = { myfinds: false }


var modificationFields = [
  'meerdere',
  'email (cloissoné)',
  'niello',
  'filigraan',
  'gegraveerd',
  'opengewerkt',
  'verguld',
  'verzilverd',
  'gedreven',
  'gedamasceerd',
  'email (groeven)',
  'onbekend',
  'andere'
]

export default {
  props: ['name', 'model', 'saved'],
  data () {
    const showFacets = ls('showFacets') || {}
    return {
      fields: window.fields,
      modificationFields: modificationFields,
      advanced: false,
      show: Object.assign({
        category: true,
        status: true,
        embargo: true,
        period: null,
        technique: null,
        modification: null,
        objectMaterial: true
      }, showFacets)
    }
  },
  computed: {
    statusOptions () {
      if (this.$root.user.administrator || this.model.myfinds) {
        return ['Gepubliceerd', 'Klaar voor validatie', 'Aan te passen', 'Voorlopige versie', 'Wordt verwijderd']
      }
      if (this.$root.user.validator) {
        return ['Gepubliceerd', 'Klaar voor validatie', 'Aan te passen']
      }
    },
    embargoOptions () {
      if (this.$root.user.administrator || this.model.myfinds) {
        return [{
          label: 'Onder embargo',
          value: true
        }, {
          label: 'Niet onder embargo',
          value: false
        }]
      }
    },
    unnamed () {
      return this.saved.filter(s => !s.name)
    }
  },
  methods: {
    restore (filter) {
      if (filter.state) {
        if (filter.name === this.name) {
          this.name = ''
          filter = backupState
        } else {
          backupState = inert(filter.state)
          this.name = filter.name
          filter = filter.state
        }
      }
      for (let key in this.model) {
        this.model[key] = filter[key] || null
      }
      for (let key in filter) {
        this.model[key] = filter[key] || null
      }
      this.$root.fetch()
    },
    toggle (filter, value) {
      if (!value) {
        this.model[filter] = !this.model[filter]
      } else {
        this.model[filter] = this.model[filter] == value ? false : value
      }
      this.name = ''
      this.model.offset = 0
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
    },
    show: {
      deep: true,
      handler () {
        ls('showFacets', this.show)
      }
    }
  },
  components: {
    Facet,
    FindEvent
  }
}
</script>
