<template>
  <div class="ui container">
    <form class="ui form" @submit.prevent="submit">
      <h3>Nieuwe collectie</h3>
      <div class="required field">
        <label>Type</label>
        <select class="ui search dropdown category" v-model="collection.collectionType" style="max-width: 16em">
          <option v-for="opt in fields.collectionType" :value="opt" v-text="opt" track-by="$index"></option>
        </select>
      </div>
      <div class="required field">
        <label>Titel</label>
        <input type="text" v-model="collection.title" placeholder="Naam van de collectie" style="max-width: 16em">
      </div>
      <div class="field">
        <label>Info</label>
        <textarea-growing id="description" :model.sync="collection.description"></textarea-growing>
      </div>
      <div class="field">
        <label>Bewaard door</label>
        <input type="text" v-model="collection.institutions" placeholder="Gescheiden door komma's">
      </div>
      <div class="field">
        <button class="ui button" :class="{ green: submittable }" :disabled="!submittable" type="submit">Bewaren</button>
      </div>
    </form>
  </div>
</template>

<script>
import Ajax from '../mixins/Ajax'

import TextareaGrowing from './TextareaGrowing';

export default {
  data () {
    return {
      fields: window.fields,
      errors: {},
      collection: {
        collectionType: 'prive collectie',
        title: '',
        description: '',
        person: [],
        object: [],

        // Transformed properties
        institutions: ''
      }
    }
  },
  computed: {
    submittable () {
      return this.collection.title && this.collection.collectionType
    }
  },
  methods: {
    formdata () {
      const { institutions } = this.collection

      // Apply outgoing transformers
      this.collection.institution = institutions.length ? institutions
        .split(',')
        .map(inst => inst.trim())
        .filter(Boolean)
        .map(inst => ({
          institutionAppellation: inst
        })) : null
     
      return this.collection
    },
    submitSuccess () {
      alert('success')
      this.errors = {}
    },
    submitError (res) {
      alert('err')
      this.errors = res.data
    }
  },
  mixins: [Ajax],
  components: {
    TextareaGrowing
  }
}
</script>
