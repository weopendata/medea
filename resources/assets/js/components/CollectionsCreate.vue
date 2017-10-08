<template>
  <div class="ui container">
    <form class="ui form" @submit.prevent="submit">
      <div class="create-cta">
        <label class="pull-right">
          <a href="/collections" class="ui button">Alle collecties</a>
        </label>
      </div>
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
        <label>Instelling</label>
        <input type="text" :value="collection.institutions" @input="setInstitution" placeholder="Gescheiden door komma's">
        <div v-for="msg in errors.institution" v-text="msg" class="input"></div>
      </div>
<!--       <div class="field" v-if="collection.identifier">
        <label>Gecureerd door</label>
        <ul v-if="collection.person.length">
          <li v-for="person in collection.person">{{ person && person.name }}</li>
        </ul>
        <select-person @select="addCurator" placeholder="Voeg curator toe"></select-person>
      </div> -->
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

import { incomingCollection, inert } from '../const.js'

export default {
  data () {
    return {
      fields: window.fields,
      collection: incomingCollection(window.initialCollection || {
        collectionType: 'prive collectie',
        description: '',
        institution: [],
        object: [],
        person: [],
        title: '',
      }),
      submitAction: '/collections' + (window.initialCollection ? '/' + window.initialCollection.identifier : ''),
      errors: {},
    }
  },
  computed: {
    submittable () {
      return this.collection.title && this.collection.collectionType
    }
  },
  methods: {
    setInstitution (evt) {
      this.$set('collection.institutions', evt.target.value)
    },
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
    submitSuccess (res) {
      this.errors = {}
      if (res.data && res.data.identifier) {
        window.location.href = '/collections' + (window.initialCollection ? '' : '/' + res.data.identifier)
      }
    },
    submitError (errors) {
      this.errors = errors.data
    },
    addCurator (person) {
      if (this.collection.identifier && person.identifier) {
        this.$http.put('/collections/' + this.collection.identifier + '/persons/' + person.identifier)
          .then(persons => {
            this.collection.person = persons
          })
          .catch(persons => {
            // TODO: show errors
           // this.errors = errors.data
            console.warn('Failed.. inserting anyway')
            this.collection.person = [person]
          })
      }
    }
  },
  watch: {
    collection: {
      deep:true,handler (c) {
        console.log(inert(c))
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
