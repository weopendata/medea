<template>
  <form class="ui form" @submit.prevent="submit" :action="'/file-uploads'" enctype="multipart/form-data"
        style="margin: 5em 0 5em;">
    <h2>Nieuw upload bstand</h2>
    <div class="card card-center cls-card upload-card" style="margin-left: 0em;">
      <div style="margin-left: 1rem;">
        <div class="field cleared">
          <label>Naam van het bestand</label>
          <input type="text" v-model="name" class="upload-input"/>
        </div>

        <div class="field cleared">
          <label>Type data</label>
          <select class="ui dropdown upload-input" v-model="type">
            <option v-for="(dataType, index) in dataTypes" :value="dataType.value">{{ dataType.label }}</option>
          </select>
        </div>

        <div class="field cleared">
          <label>CSV bestand</label>
          <input type="file" accept=".csv" v-on:change="onFileChange" class="upload-input"/>
        </div>

        <div class="card-textual" style="padding-left: 0px;">
          <p>
            <button type="submit" class="ui button" :class="{green:submittable}" :disabled="!submittable">Toevoegen
            </button>
            <button type="submit" class="ui button" @click.prevent="cancel">Annuleren</button>
          </p>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  export default {
    name: "CreateUpload",
    data() {
      return {
        file: null,
        name: '',
        type: 'excavation',
        dataTypes: [
          {
            label: 'Opgravingen',
            value: 'excavation',
          },
          {
            label: 'Contexten',
            value: 'context',
          },
          {
            label: 'Vondsten',
            value: 'find',
          },
          {
            label: 'Vondsten - PAN',
            value: 'pan'
          }
        ]
      }
    },
    computed: {
      submittable() {
        return this.name && this.name.length > 2 && this.file
      }
    },
    methods: {
      onFileChange(e) {
        this.file = e.target.files[0];
      },
      submit() {
        let formData = new FormData()
        formData.append('file', this.file)
        formData.append('name', this.name)
        formData.append('type', this.type)

        const config = {
          headers: {'content-type': 'multipart/form-data'}
        }

        axios
          .post('/file-uploads', formData, config)
          .then(response => {
            this.file = null
            this.name = ''

            this.$emit('uploadCreated')
          })
          .catch(error => {
            console.log(error)
          })
      },
      cancel() {
        this.name = ''
        this.file = null

        this.$emit('hide')
      }
    }
  }
</script>

<style scoped>
  .upload-card {
    width: 600px;
  }

  .upload-input {
    width: 70% !important;
  }
</style>
