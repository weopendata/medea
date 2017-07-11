<template>
  <div class="ui container">
    <div class="create-cta">
      <a href="/collections" class="ui button">Alle collecties</a>
    </div>
    <form class="ui form" @submit.prevent="submit">
      <h3 v-if="collection.identifier">Collectie bewerken</h3>
      <h3 v-else>Nieuwe collectie maken</h3>
      <div class="required field">
        <label>Type</label>
        <select class="ui search dropdown category" v-model="collection.collectionType" style="max-width: 16em">
          <option v-for="opt in fields.collectionType" :value="opt" v-text="opt" track-by="$index"></option>
        </select>
      </div>
      <div class="required field" :class="{ error: errors.title }">
        <label>Titel</label>
        <input type="text" v-model="collection.title" placeholder="Naam van de collectie" style="max-width: 16em">
        <div v-for="msg in errors.title" v-text="msg" class="input"></div>
      </div>
      <div class="field" :class="{ error: errors.description }">
        <label>Info</label>
        <textarea-growing id="description" :model.sync="collection.description"></textarea-growing>
        <div v-for="msg in errors.description" v-text="msg" class="input"></div>
      </div>
      <div class="field" :class="{ error: errors.institution }">
        <label>Bewaard door</label>
        <input type="text" v-model="collection.institutions" placeholder="Gescheiden door komma's">
        <div v-for="msg in errors.institution" v-text="msg" class="input"></div>
      </div>
      <div class="field" v-if="collection.identifier">
        <label>Gecureerd door</label>
        <ul v-if="collection.person.length">
          <li v-for="person in collection.person">{{ person && person.name }}</li>
        </ul>
        <select-person @select="addCurator" placeholder="Voeg curator toe"></select-person>
      </div>
      <div class="field">
        <button class="ui button" :class="{ green: submittable }" :disabled="!submittable" type="submit">Bewaren</button>
      </div>
    </form>
  </div>
</template>

<script>
import Ajax from '../mixins/Ajax'

import SelectPerson from './SelectPerson'
import TextareaGrowing from './TextareaGrowing'

export default {
  data () {
    return {
      fields: window.fields,
      collection: {
        collectionType: 'prive collectie',
        title: '',
        description: '',
        person: [],
        object: [],

        // Transformed properties
        institutions: ''
      },
      submitAction: '/collections',
      errors: {},
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
      this.errors = {}
    },
    submitError (errors) {
      this.errors = errors.data
    },
    addCurator (person) {
      if (this.collection.identifier) {
        this.$http.post('/collections/' + this.collection.identifier + '/people')
          .then(people => {
            this.collection.person = people
          })
      }
    }
  },
  mixins: [Ajax],
  components: {
    SelectPerson,
    TextareaGrowing
  }
}
</script>
