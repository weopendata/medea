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

          <template>
            <dt>Type:</dt>
            <dd>{{ uploadType }}</dd>
          </template>

          <dt>
            Geupload door:
          </dt>
          <dd>{{ selectedUpload.user_name }}</dd>

          <template v-if="selectedUpload.status">
            <dt>Status:</dt>
            <dd>{{ uploadStatus }}</dd>
          </template>
        </dl>
      </div>

      <div class="uploads-overview__upload-actions">
        <button class="ui green button" @click="startUpload()" :disabled="startingUpload" v-if="canInteractWithUpload">
          Start import
        </button>
        <button class="ui red button" @click="deleteUpload()" :disabled="startingUpload" v-if="canInteractWithUpload"><i
                class="trash icon"></i>Delete upload bestand
        </button>
      </div>

      <template v-if="this.selectedUpload && this.selectedUpload.id">
        <div class="uploads-overview__upload_logs">
          <h4>Import logs</h4>

          <select class="ui search dropdown category" v-model="selectedImport" style="max-width: 30em;"
                  v-if="this.selectedUpload.import_jobs.length > 0">
            <option v-for="importJob in this.selectedUpload.import_jobs" :value="importJob"
                    v-text="importJobDisplayName(importJob)"></option>
          </select>

          <table v-if="logs.length" class="uploads-overview__log-table">
            <thead>
            <tr>
              <th class="uploads-overview__log-column-line-number">Lijn</th>
              <th class="uploads-overview__log-column-action">Actie</th>
              <th class="uploads-overview__log-column-action">Status</th>
              <th class="uploads-overview__log-column-description">Beschrijving</th>
              <th class="uploads-overview__log-column-action"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(log, index) in logs" :key="'upload_log_' + index">
              <td class="uploads-overview__log-column-line-number" style="padding-left: 15px;">
                {{ log.line_number }}
              </td>
              <td class="uploads-overview__log-column-action">
                {{ log.action }}
              </td>
              <td class="uploads-overview__log-column-action">
                {{ log.status }}
              </td>
              <td class="uploads-overview__log-column-description">
                {{ log.message }}
              </td>
              <td class="uploads-overview__log-column-action" v-if="log.object_identifier && (selectedUpload && selectedUpload.type) == 'find'">
                <button class="mini ui button" @click="goToFind(log.object_identifier)"><i class="eye icon"></i>Bekijk </button>
              </td>
              <td class="uploads-overview__log-column-action" v-else>
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
      },
      uploadStatus() {
        if (!this.selectedUpload || !this.selectedUpload.status) {
          return
        }

        switch (this.selectedUpload.status) {
          case 'queued':
            return 'In wachtrij'
          case 'finished':
            return 'Klaar'
        }
      },
      uploadType() {
        if (!this.selectedUpload || !this.selectedUpload.status) {
          return
        }

        switch (this.selectedUpload.type) {
          case 'excavation':
            return 'Opgravingen'
          case 'context':
            return 'Contexten'
          case 'find':
            return 'Vondsten'
        }
      }
    },
    methods: {
      importJobDisplayName(importJob) {
        return 'Upload of ' + importJob.created_at + ' - Status: ' + importJob.status
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

        if (!this.selectedImport || !this.selectedImport.id) {
          return
        }

        axios.get('/api/uploads/' + this.selectedImport.id + '/logs')
          .then(response => {
            this.logs = response.data
          })
          .catch(error => {
            console.log(error)
          })
      },
      goToFind (identifier) {
        window.open('/finds/' + identifier, '_blank')
      }
    },
    watch: {
      uploads(v) {
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

<style scoped lang="scss">
  .uploads-overview__upload-actions {
    margin-top: 1.5rem;
  }

  .uploads-overview__log-table {
    margin-top: 1.5rem;
    border: 1px solid #CECECE;
    border-collapse: collapse;
    width: 100%;
    max-width: 800px;

    tr {
      border-bottom: 1px solid #CECECE;
    }
  }

  .uploads-overview__upload_logs {
    margin-top: 1.5rem;
  }

  .uploads-overview__log-column-line-number {
    width: 35px;
  }

  .uploads-overview__log-column-action {
    text-align: center;
    width: 100px;
  }

  .uploads-overview__log-column-description {
    width: 500px;
    text-align: left;
  }

  .uploads-overview__log-column-action {
    width: 100px;
    text-align: center;
  }
</style>
