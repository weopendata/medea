<template>
  <div>
    <div class="ui container">
      <div v-if="displayCreateUpload">
        <create-upload @uploadCreated="fetchUploads" @hide="displayCreateUpload = false"></create-upload>
      </div>

      <div>
        <uploads-overview :uploads="uploads" @displayCreateUpload="displayCreateUpload = true"></uploads-overview>
      </div>
    </div>
  </div>
</template>

<script>
  import CreateUpload from "./CreateUpload.vue";
  import UploadsOverview from "./UploadsOverview.vue";

  export default {
    name: "Uploads.vue",
    data () {
      return {
        uploads: [],
        displayCreateUpload: false
      }
    },
    methods: {
      fetchUploads () {
        axios.get('/api/uploads')
          .then(response => {
            var uploads = response.data

            this.uploads = uploads
          })
          .catch(error => {
            console.log(error)
          })
      }
    },
    mounted() {
      this.fetchUploads()
    },
    components: {
      CreateUpload,
      UploadsOverview
    }
  }
</script>

<style scoped>

</style>
