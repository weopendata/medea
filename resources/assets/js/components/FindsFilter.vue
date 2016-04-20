<template>
  <div class="ui form" @change="$root.fetch()">
    <div class="fields">
      <div class="pct40 wide field">
        <form class="ui action input" @submit.prevent="$root.fetch()">
          <input type="text" v-model="model.query" placeholder="Zoeken...">
          <button class="ui icon button" :class="{green:model.query}">
            <i class="search icon"></i>
          </button>
        </form>
      </div>
      <div class="pct60 wide field" style="line-height:37px;">
        <div class="ui dropdown button simple" v-if="saved&&saved.length&&!advanced">
          <span class="text">Bewaarde filters</span>
          <div class="menu">
            <div class="item" v-for="filter in saved" v-text="filter.name" @click="restore(filter)"></div>
          </div>
        </div> &nbsp;
        <button v-if="!$root.user.isGuest" class="ui button" :class="{green:model.myfinds}" @click.prevent="toggleMyfinds">Mijn vondsten</button> &nbsp;
        <a @click.prevent="advanced=true" v-if="!advanced">Geavanceerd zoeken</a>
        <span class="finds-order" v-if="advanced">
          Sorteren op:
          <a @click.prevent="sortBy('findDate')" :class="{active:model.order=='findDate', reverse:model.order=='-findDate'}">Datum</a>
          <a @click.prevent="sortBy('production')" :class="{active:model.order=='production', reverse:model.order=='-production'}">Cultuur</a>
          <a @click.prevent="sortBy('dimensions')" :class="{active:model.order=='dimensions', reverse:model.order=='-dimensions'}">Grootte</a>
        </span>
      </div>
    </div>
    <div class="equal width fields" v-if="advanced">
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.category">
          <option value="*">Alle categorieën</option>
          <option v-for="opt in fields.object.category" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.culture">
          <option value="*">Alle culturen</option>
          <option v-for="opt in fields.classification.culture" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.material">
          <option value="*">Alle materialen</option>
          <option v-for="opt in fields.object.material" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <select class="ui search fluid dropdown category" v-model="model.technique">
          <option value="*">Alle technieken</option>
          <option v-for="opt in fields.object.technique" :value="opt" v-text="opt"></option>
        </select>
      </div>
    </div>
    <div class="fields" v-if="advanced&&$root.user.validator">
      <div class="four wide field">
        <select class="ui search fluid dropdown category" v-model="model.status">
          <option value="in bewerking">in bewerking</option>
          <option value="gevalideerd">gevalideerd</option>
          <option value="revisie nodig">revisie nodig</option>
          <option value="onder embargo" v-if="$root.user.admin">embargo</option>
          <option value="verwijderd" v-if="$root.user.admin">afgekeurd</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script>
import FindEvent from './FindEvent';

import transition from 'semantic-ui-css/components/transition.min.js';
import dropdown from 'semantic-ui-css/components/dropdown.min.js';

export default {
  props: ['model', 'saved'],
  data () {
    return {
      fields: window.fields,
      advanced: false
    }
  },
  methods: {
    relevant (find) {
      console.log('rel')
      return find.object.objectValidationStatus == 'gevalideerd'
      || (this.user.validator && find.object.objectValidationStatus == 'in bewerking')
    }
  },
  ready () {
    $('.ui.dropdown').dropdown()
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