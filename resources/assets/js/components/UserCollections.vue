<template>
  <div class="ui form">
    <div class="collections">
      <div class="collection" v-for="collection in collections">
        <span class="remove" @click="remove(collection)">&times;</span>
        <a :href="'/collections/' + collection.identifier">{{ collection.title }}</a>
      </div>
    </div>
    <div class="field collections__add" :class="{error:errors.function}">
      <label for="function">Collectie toewijzen aan dit profiel</label>
      <select-collection @select="assignCollection" placeholder="Zoek collectie"></select-collection>
      <div v-for="msg in errors.person" v-text="msg" class="input"></div>
    </div>
  </div>
</template>

<script>
import Ajax from '../mixins/Ajax'

import SelectCollection from './SelectCollection'

export default {
  data () {
    return {
      errors: {},
      collections: window.collections || [],
      profile: window.profile,
    }
  },
  methods: {
    assignCollection (collection) {
      this.$http.put('/collections/' + collection.identifier + '/persons/' + this.profile.identifier)
        .then(persons => {
          this.errors = {}
          this.collections.push(collection)
        })
        .catch(errors => {
          this.errors = errors.data
        })
    },
    remove (collection) {
      this.$http.delete('/collections/' + collection.identifier + '/persons/' + this.profile.identifier)
        .then(persons => {
          this.errors = {}
          this.collections.splice(this.collections.indexOf(collection), 1)
        })
        .catch(errors => {
          this.errors = errors.data
        })
    }
  },
  components: {
    SelectCollection
  }
}
</script>

<style lang="sass">
.collections {
  margin-bottom: 1em;
}
.collection {
  padding: 5px 10px;
}
.remove {
  margin-right: .5rem;
  cursor: pointer;
  padding: 5px 10px;
  &:hover {
    background: red;
    color: white;
  }
}
</style>
