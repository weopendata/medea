<template>
  <div class="ui action input" v-if="model.identifier" :class="{error:model.error}">
    <input type="text" :value="cite(t)" readonly>
    <button type="button" class="ui basic icon button" @click="$parent.rmPublication(index)">
      <i class="unlinkify icon"></i>
    </button>
  </div>
  <div class="ui action input" v-else>
    <input type="text" v-model="model.publicationTitle" placeholder="bibliografische referentie, URL, DOI, ..." readonly>
    <button type="button" class="ui button" @click="$parent.editPublication(model, index)">Bewerken</button>
    <button type="button" class="ui basic icon button" @click="$parent.rmPublication(index)">
      <i class="unlinkify icon"></i>
    </button>
  </div>
</template>

<script>
import {inert, fromPublication} from '../const.js'

import Publication from '../mixins/Publication.js'

export default {
  props: ['model', 'index'],
  mixins: [Publication],
  computed: {
    t () {
      return fromPublication(this.fetchedPub)
    },
    fetchedPub () {
      if (!this.model.fetching) {
        this.model.fetching = true
        this.fetch(this.model.identifier)
      }
      if (!this.model.fetched) {
        return {}
      }
      return this.model
    }
  },
  methods: {
    fetch (id) {

      this.$http.get('/api/publications/' + id).then(function ({ data }) {
        Object.assign(this.model, data, {
          fetched: true
        })
      }, function () {
        Object.assign(this.model, {
          error: true
        })
      })
    }
  }
}
</script>
