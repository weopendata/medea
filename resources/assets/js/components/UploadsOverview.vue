<template>
  <div class="ui container">
    <h2>Upload overzicht</h2>

    <div>
      <select class="ui search dropdown category" v-model="selectedUpload" style="max-width: 45em;"
              v-if="uploads.length > 0">
        <option v-for="upload in uploads" :value="upload" v-text="uploadDisplayName(upload)" track-by="$index"></option>
      </select>

      <div class="ui labeled button" tabindex="0" @click.prevent="displayAddUpload">
        <div class="ui green button">
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

          <template>
            <dt>Type:</dt>
            <dd>{{ selectedUpload.type }}</dd>
          </template>
        </dl>
      </div>

      <div>
        <button class="ui green button" @click="startUpload()" :disabled="startingUpload" v-if="canInteractWithUpload">
          Start import
        </button>
        <button class="ui red button" @click="deleteUpload()" :disabled="startingUpload" v-if="canInteractWithUpload"><i
                class="trash icon"></i>Delete upload bestand
        </button>
      </div>

      <template v-if="this.selectedUpload && this.selectedUpload.id">
        <div style="margin-top: 1rem;">
          <h4>Import logs</h4>

          <select class="ui search dropdown category" v-model="selectedImport" style="max-width: 30em;"
                  v-if="this.selectedUpload.import_jobs.length > 0">
            <option v-for="importJob in this.selectedUpload.import_jobs" :value="importJob"
                    v-text="importJobDisplayName(importJob)"></option>
          </select>

          <table v-if="logs.length">
            <thead>
            <tr>
              <th>Lijn</th>
              <th>Actie</th>
              <th>Status</th>
              <th>Beschrijving</th>
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
              <td>
                {{ log.message }}
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
  export default {
    name: "UploadsOverview",
    props: ['uploads'],
    data() {
      return {
        selectedUpload: {},
        selectedImport: {},
        logs: [],
        startingUpload: false
      }
    },
    computed: {
      canInteractWithUpload() {
        return this.selectedUpload.status === 'finished' || this.selectedUpload.status === 'not imported'
      }
    },
    methods: {
      importJobDisplayName(importJob) {
        return 'Upload of ' +  importJob.created_at + ' - Status: ' + importJob.status
      },
      uploadDisplayName(upload) {
        return upload.name + ' - ' + upload.user_name + ' (' + upload.created_at + ')'
      },
      displayAddUpload() {
        this.$emit('displayCreateUpload')
      },
      startUpload() {
        this.startingUpload = true

        axios.post('/api/uploads/' + this.selectedUpload.id + '/upload')
          .then(response => {
            this.selectedUpload.status = 'queued';
            this.startingUpload = false
          })
          .catch(error => {
            console.log(error)

            this.startingUpload = false
          })
      },
      deleteUpload() {
        if (!confirm('Ben je zeker dat dit upload bestand verwijderd mag worden? Het bestand en alle logs die door de imports van dit bestand werden gegenereerd zullen ook verwijderd worden. Geïmporteerde data blijft in het platform.')) {
          return
        }

        this.$emit('deleteUpload', {id: this.selectedUpload.id})

        this.selectedUpload = {}
      },
      fetchLogs() {
       if (!this.selectedImport) {
         this.logs = []

         return
       }

        axios.get('/api/uploads/' + this.selectedImport.id + '/logs')
          .then(response => {
            this.logs = response.data
          })
          .catch(error => {
            console.log(error)
          })
      }
    },
    watch: {
      uploads (v) {
        if (!v || !v.length > 0) {
          return
        }

        this.selectedUpload = v[0]
      },
      selectedUpload(v) {
        this.selectedImport = {}
      },
      selectedImport(v) {
        if (!v) {
          this.logs = []

          return
        }

        this.fetchLogs()
      }
    }
  }
</script>

<style scoped>

</style>
