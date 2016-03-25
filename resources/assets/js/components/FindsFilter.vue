<template>
  <div class="ui form" @change="change">
    <div class="fields">
      <div class="field">
        <label>Categorie</label>
        <select class="ui dropdown" v-model="model.category">
          <option>Alle</option>
          <option value="d">Alsle</option>
          <option v-for="opt in filters.category" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <label>Myfinds</label>
        <input type="checkbox" v-model="model.myfinds" value="true">
      </div>
      <div class="field">
        <button  class="ui button" :class="{purple:model.myfinds}" @click.prevent="model.myfinds=!model.myfinds">Mijn vondsten</button>
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
    change () {
      var model = this.model
      var query = Object.keys(this.model).map(function(key, index) {
        return key + '=' + encodeURIComponent(model[key]);
      }).join('&');
      console.log(query, this.model, this.query)
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