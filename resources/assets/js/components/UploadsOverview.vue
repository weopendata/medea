<template>
  <div class="ui container">
    <h2>Upload overzicht</h2>

    <div>
      <select class="ui search dropdown category" v-model="selectedUpload" style="max-width: 16em">
        <option v-for="upload in uploads" :value="upload" v-text="uploadDisplayName(upload)" track-by="$index"></option>
      </select>

      <div class="ui labeled button" tabindex="0" @click.prevent="displayAddUpload">
        <div class="ui button">
          <i class="plus icon"></i> Voeg bestand toe
        </div>
      </div>
    </div>

    <div v-if="selectedUpload && selectedUpload.id" style="margin-top: 1rem;">
      <div>
        <h4>Upload details</h4>

        <dl>
          <dt>
            Naam:
          </dt>
          <dd>{{ selectedUpload.name }}</dd>

          <dt>
            Geupload door:
          </dt>
          <dd>{{ selectedUpload.user_name }}</dd>

          <template v-if="selectedUpload.status">
            <dt>Status:</dt>
            <dd>{{ selectedUpload.status }}</dd>
          </template>

          <template v-if="selectedUpload.last_imported">
            <dt>Laatst geimporteerd op:</dt>
            <dd>{{ selectedUpload.last_imported }}</dd>
          </template>
        </dl>
      </div>

      <div style="margin-top: 1rem;">
        <h4>Import logs</h4>
        <table>
          <thead>
            <tr>
              <th>Lijn</th>
              <th>Actie</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(log, index) in logs" :key="'upload_log_' + index">
              <td>
                {{ log.line_number }}
              </td>
              <td>
                {{ log.action }}
              </td>
              <td>
                {{ log.status }}
              </td>
            </tr>
          </tbody>
        </table>
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
        selectedUpload: {},
        logs: []
      }
    },
    methods: {
      uploadDisplayName (upload) {
        return upload.name + ' - ' + upload.user_name + ' (' + upload.created_at + ')'
      },
      displayAddUpload () {
        this.$emit('displayCreateUpload')
      },
      fetchLogs () {
        if (!this.selectedUpload.id || !this.selectedUpload.import_jobs || this.selectedUpload.import_jobs.length === 0) {
          return
        }

        let jobId = this.selectedUpload.import_jobs[0].id

        axios.get('/api/uploads/' + jobId + '/logs')
          .then(response => {
            this.logs = response.data
          })
          .catch(error => {
            console.log(error)
          })
      }
    },
    watch: {
      selectedUpload (v) {
        this.fetchLogs()
      }
    }
  }
</script>

<style scoped>

</style>
