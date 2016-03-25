<template>
  <div class="ui form" @change="change">
    <div class="equal width fields">
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
      <div class="field">
        <button  class="ui fluid button" :class="{purple:model.myfinds}" @click.prevent="toggleMyfinds">Mijn vondsten</button>
      </div>
    </div>
  </div>
</template>

<script>
import FindEvent from './FindEvent';

import transition from 'semantic-ui-css/components/transition.min.js';
import dropdown from 'semantic-ui-css/components/dropdown.min.js';

export default {
  props: ['model'],
  data () {
    return {
      fields: window.fields,
      query: ''
    }
  },
  methods: {
    relevant (find) {
      console.log('rel')
      return find.object.objectValidationStatus == 'gevalideerd'
      || (this.user.isValidator && find.object.objectValidationStatus == 'in bewerking')
    }
  },
  ready () {

    $('.ui.dropdown').dropdown()
  },
  methods: {
    toggleMyfinds () {
      this.model.myfinds = !this.model.myfinds
      this.change()
    },
    change () {
      var model = this.model
      var query = Object.keys(this.model).map(function(key, index) {
        return model[key] && model[key] !== '*' ? key + '=' + encodeURIComponent(model[key]) : null;
      }).filter(Boolean).join('&');
      if (this.query !== query) {
        this.query = query
        this.$root.fetch(query)
      }
    }
  },
  components: {
    FindEvent
  }
}
</script>