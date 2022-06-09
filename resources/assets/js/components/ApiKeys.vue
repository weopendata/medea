<template>
  <div class="ui container">
    <div class="ui form">
      <div class="field">
        <label>API-key name</label>
        <input type="text" v-model="apiKeyName" placeholder="API-key name" style="max-width: 10rem;">
        <button class="ui button" :class="{green : canCreateNewKey}" :disabled="!canCreateNewKey" type="submit" @click.prevent="createNewKey">
          Create
        </button>
      </div>
    </div>

    <div class="api-keys-container">
      <h3>API keys</h3>
      <table class="ui unstackable table" style="text-align:center;min-width:600px;width:100%">
        <thead>
        <tr>
          <th style="width:50px;">Name</th>
          <th>API key</th>
          <th></th>
        </tr>
        </thead>
        <tr is="ApiKeyRow" v-for="apiKey in apiKeys" @remove="removeApiKey" :api-key="apiKey"></tr>
      </table>
    </div>
  </div>
</template>

<script>
import ApiKeyRow from './ApiKeyRow.vue'

export default {
  name: 'ApiKeys',
  components: { ApiKeyRow },
  data () {
    return {
      apiKeyName: '',
      apiKeys: []
    }
  },
  computed: {
    canCreateNewKey () {
      return this.apiKeyName.length > 2
    }
  },
  methods: {
    createNewKey () {
      if (!this.canCreateNewKey) {
        return
      }

      axios
          .post('api-keys', { name: this.apiKeyName })
          .then(response => {
            this.apiKeyName = ''

            this.fetchApiKeys()
          })
    },
    fetchApiKeys () {
      axios
          .get('/api/api-keys')
          .then(response => {
            this.apiKeys = response.data
          })
    },
    removeApiKey (apiKey) {
      axios
          .delete('/api-keys/' + apiKey.id)
          .then(response => {
            this.fetchApiKeys()
          })
    }
  },
  mounted () {
    this.fetchApiKeys()
  }
}
</script>

<style scoped>
.api-keys-container {
  margin-top: 2rem;
}
</style>