<template>
  <div class="ui form" @change="filtersChanged()">
    <div class="field">
      <form class="ui action input" @submit.prevent="filtersChanged()">
        <input type="text" v-model="model.query" placeholder="Zoeken..." style="width:100px">
        <button class="ui icon button" :class="{green:model.query}">
          <i class="search icon"></i>
        </button>
      </form>
    </div>

    <div class="ui" style="margin-bottom: 1rem;">
      <div class="field">
        <button class="ui icon button" @click="resetFilters()">
          Reset filters
          <i class="refresh icon"></i>
        </button>
      </div>
    </div>

    <div class="facets" style="padding: 0.5rem;" v-if="panIdFilter">
      <div class="pan-id-filter-line">
        Alle vondsten zijn momenteel gefilterd op typologie "{{ panIdFilterLabel }}"
      </div>
      <div class="pan-id-filter-line">
        <a :href="'/typology-browser#' + panIdFilter">Bekijk de typologie {{ panIdFilterLabel }}</a>
      </div>
      <div class="pan-id-filter-line">
        <a href="" @click.prevent="removePanIdFilter()">Verwijder de typologie filter</a>
      </div>
    </div>

    <div style="display: flex; justify-content: space-between;">
      <h3>Filter opties</h3>
      <span style="margin-top: 0.2rem;" v-if="fetching">(bezig met bijwerken...)</span>
    </div>

    <div class="facets">
      <div v-if="user.email && !isApplicationPublic" class="facet">
        <h3 class="facet-title"><i class="ui star icon"></i> Favorieten</h3>
        <a href="#" class="facet-a" :class="{active:name=='$val'}"
           @click.prevent="restore({name:'$val', state:{status:'Klaar voor validatie'}})" v-if="user.validator">Te
          valideren vondsten</a>
        <a href="#" class="facet-a" :class="{active:model.myfinds}" @click.prevent="toggle('myfinds', true)">Mijn
          vondsten</a>
        <a href="#" class="facet-a" :class="{active:name==fav.name}" @click.prevent="restore(fav)" v-for="fav in saved"
           v-text="fav.name"></a>
      </div>

      <div class="facet" v-if="canFilterOnPanTypologyDates">
        <h3 class="facet-title">Datering</h3>
        <div class="facet-date-container">
          <div style="margin-right: 0.5rem; margin-left: 1rem;">van - tot:</div>
          <input type="number" v-model="model.startYear" class="facet-date-filter"/>
          <div>&nbsp;-&nbsp;</div>
          <input type="number" v-model="model.endYear" class="facet-date-filter"/>
        </div>
      </div>

      <facet label="Validatie status" prop="status" :options="statusOptions" v-if="!isApplicationPublic"></facet>
      <facet label="Embargo" prop="embargo" :options="embargoOptions"></facet>
      <facet
          v-for="(facet, index) in dynamicFacetOptions"
          :key="'facet_option_' + index"
          :label="facet.label"
          :prop="facet.prop"
          :options="facet.options"
          :disabled="fetching"
      />
    </div>
  </div>
</template>

<script>
import ls from 'local-storage'

import FindEvent from './FindEvent.vue';
import Facet from './Facet.vue';
import { inert } from '../const.js';
import GlobalSettings from '../mixins/GlobalSettings';

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
  props: ['name', 'fetching', 'model', 'saved', 'facets', 'excludedFacets'],
  data () {
    const showFacets = ls('showFacets') || {}

    return {
      fields: window.fields,
      user: window.medeaUser || {},
      modificationFields: modificationFields,
      advanced: false,
      backupState: { myfinds: false },
      show: Object.assign({
        category: true,
        status: true,
        embargo: true,
        period: null,
        technique: null,
        modification: null,
        objectMaterial: true,
        volledigheid: null,
        merkteken: null,
        opschrift: null,
        collections: null,
        photographCaption: null,
        collection: true,
        findSpotLocation: null,
        excavationLocation: null
      }, showFacets)
    }
  },
  computed: {
    panIdFilter () {
      return this.model.panid
    },
    panIdFilterLabel () {
      if (this.model.panidLabel) {
        return this.model.panidLabel
      }

      return this.model.panid
    },
    canFilterOnPanTypologyDates () {
      if (!this.excludedFacets) {
        return true
      }

      return !this.excludedFacets.includes('datering')
    },
    dynamicFacetOptions () {
      // Make a copy of the component property to trigger the reactive return value of this computed property
      const facets = this.facets

      return [
        {
          label: 'Foto',
          prop: 'photographCaption',
          options: this.getFilterFacetOptions('photographCaptionPresent', facets)
        },
        {
          label: 'Categorie',
          prop: 'category',
          options: this.getFilterFacetOptions('category', facets)
        },
        {
          label: 'Periode',
          prop: 'period',
          options: this.getFilterFacetOptions('period', facets)
        },
        {
          label: 'Materiaal',
          prop: 'objectMaterial',
          options: this.getFilterFacetOptions('material', facets)
        },
        {
          label: 'Locatie',
          prop: 'findSpotLocation',
          options: this.getFilterFacetOptions('findSpotLocality', facets)
        },
        {
          label: 'Locatie',
          prop: 'excavationLocation',
          options: this.getFilterFacetOptions('excavationLocality', facets)
        },
        {
          label: 'Oppervlaktebehandeling',
          prop: 'modification',
          options: this.getFilterFacetOptions('modification', facets)
        },
        {
          label: 'Volledig',
          prop: 'volledigheid',
          options: this.getFilterFacetOptions('complete', facets)
        },
        {
          label: 'Merkteken',
          prop: 'merkteken',
          options: this.getFilterFacetOptions('mark', facets)
        },
        {
          label: 'Opschrift',
          prop: 'opschrift',
          options: this.getFilterFacetOptions('insignia', facets)
        },
        {
          label: 'Collecties',
          prop: 'collection',
          options: this.collectionFacetOptions
        }
      ].filter(dynamicFacet => dynamicFacet.options && dynamicFacet.options.length > 0 && !this.excludedFacets.includes(dynamicFacet.prop))
    },
    collectionFacetOptions () {
      const options = this.getFilterFacetOptions('collection', this.facets)

      return [...this.fields.collections].filter(field => options.includes(field.value))
    },
    statusOptions () {
      if (!this.user) {
        return;
      }

      if (this.user.administrator || this.model.myfinds) {
        return ['Gepubliceerd', 'Klaar voor validatie', 'Aan te passen', 'Voorlopige versie', 'Wordt verwijderd']
      }
      if (this.user.validator) {
        return ['Gepubliceerd', 'Klaar voor validatie', 'Aan te passen']
      }
    },
    embargoOptions () {
      if (!this.user) {
        return;
      }

      if (this.user.administrator || this.model.myfinds) {
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
      return this.savedSearches.filter(s => !s.name)
    }
  },
  methods: {
    removePanIdFilter () {
      this.model['panid'] = null

      this.$emit('filtersChanged')
    },
    getFilterFacetOptions(facetName, facets) {
      var options = facets[facetName] || []

      return this.appendActiveFilterToFacetOptions(facetName, options)
    },
    appendActiveFilterToFacetOptions(facetName, options) {
      if (
          this.model
          && this.model[facetName]
          && this.model[facetName] !== '*'
          && (typeof this.model[facetName] === 'string' || !!isNaN(this.model[facetName]))
          && !options.includes(this.model[facetName])
      ) {
        options.push(this.model[facetName])
      }

      return options
    },
    resetFilters () {
      this.$parent.resetFilters()
    },
    filtersChanged () {
      this.$emit('filtersChanged');
    },
    restore (filter) {
      if (filter.state) {
        if (filter.name === this.name) {
          this.name = ''
          filter = this.backupState
        } else {
          this.backupState = inert(filter.state)
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

      this.$emit('filtersChanged');
    },
    toggle (filter, value) {
      if (!value) {
        this.model[filter] = !this.model[filter]
      } else {
        this.model[filter] = this.model[filter] == value ? false : value
      }

      this.name = ''
      this.model.offset = 0

      this.$emit('filtersChanged');
    },
    toggleMyfinds () {
      this.model.myfinds = this.model.myfinds ? false : 'yes';
      this.$emit('filtersChanged');
    },
    sortBy (type) {
      if (this.model.order == type) {
        this.model.order = '-' + type
      } else if (this.model.order == '-' + type) {
        this.model.order = false
      } else {
        this.model.order = type
      }

      this.$emit('filtersChanged');
    }
  },
  mounted () {
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
  mixins: [GlobalSettings],
  components: {
    Facet,
    FindEvent
  }
}
</script>

<style scoped>
input[type='number'] {
  -moz-appearance:textfield;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
}

.pan-id-filter-line {
  margin-bottom: 0.5rem;
}
</style>