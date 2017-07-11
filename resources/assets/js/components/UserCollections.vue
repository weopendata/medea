<template>
  <div class="ui form">
    <ul>
      <li v-for="collection in collections">{{ collection.title }}</li>
    </ul>
    <div class="field" :class="{error:errors.function}">
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
    }
  },
  components: {
    SelectCollection
  }
}
</script>