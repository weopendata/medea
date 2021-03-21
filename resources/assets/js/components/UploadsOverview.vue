<template>
  <div class="ui container">
    <h2>Upload overzicht</h2>

    <div>
      <select class="ui search dropdown category" v-model="selectedUpload" style="max-width: 16em">
        <option v-for="upload in uploads" :value="upload.id" v-text="uploadDisplayName(upload)" track-by="$index"></option>
      </select>

      <div class="ui labeled button" tabindex="0" @click.prevent="displayAddUpload">
        <div class="ui button">
          <i class="plus icon"></i> Voeg bestand toe
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: "UploadsOverview",
    props: ['uploads'],
    data () {
      return {
        selectedUpload: ''
      }
    },
    methods: {
      uploadDisplayName (upload) {
        return upload.name + ' - ' + upload.user_name + ' (' + upload.created_at + ')'
      },
      displayAddUpload () {
        this.$emit('displayCreateUpload')
      }
    }
  }
</script>

<style scoped>

</style>
