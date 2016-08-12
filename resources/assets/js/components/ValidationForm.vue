<template>
  <div class="ui form" @submit.prevent="submit" :action="submitAction">
    <h3>Vondst valideren</h3>
    <div class="ui two columns doubling grid">
      <div class="column">
        <div class="field">
          <label>Is deze vonstfiche klaar voor publicatie? Duid aan wat van toepassing is.</label>
          <div class="ui checkbox">
            <input type="checkbox" tabindex="0" class="hidden" v-model="remove">
            <label>Deze vondst hoort niet thuis op MEDEA</label>
          </div>
        </div>
        <div class="field">
          <div class="ui checkbox">
            <input type="checkbox" tabindex="0" class="hidden" v-model="embargo">
            <label>Deze vondstfiche bevat gevoelige informatie</label>
          </div>
        </div>
      </div>
      <div class="column">
        <div class="field">
          <label for="description">Geef feedback mee aan de detectorist over de gevraagde/gedane aanpassingen:</label>
          <textarea-growing id="description" :model.sync="remarks"></textarea-growing>
        </div>
      </div>
    </div>
    <photo-validation :model="remarks" :index="index" v-for="(index, remarks) in imgRemarks"></photo-validation>
    <p v-if="result" v-text="result"></p>
    <p v-if="!embargo&&!remove&&valid">
      <button @click="post('gevalideerd')" class="ui green big button" :class="{green:valid}" :disabled="!valid">
        <i class="thumbs up icon"></i> Goedkeuren
      </button>
    </p>
    <p v-if="!embargo&&!remove&&!valid">
      <button @click="post('revisie nodig')" class="ui orange big button">Aanpassen</button>
    </p>
    <p v-if="embargo">
      <button @click="post('onder embargo')" class="ui orange big button">Embargo</button>
    </p>
    <p v-if="remove">
      <button @click="post('verwijderd')" class="ui red big button">Afwijzen</button>
    </p>
  </div>
</template>

<script>
import PhotoValidation from './PhotoValidation';
import TextareaGrowing from './TextareaGrowing';

export default {
  props: ['obj', 'feedback'],
  data () {
    return {
      embargo: false,
      imgRemarks: {},
      remarks: '',
      remove: false,
      submitting: false,
      status: null,
      result: false
    }
  },
  computed: {
    valid () {
      return !this.remarks && !Object.keys(this.imgRemarks).length && !Object.keys(this.feedback).length
    }
  },
  methods: {
    submitSuccess ({data}) {
      console.log('Validation', data)
      this.result = data.success ? 'Status van de vondst: ' + this.status : 'Er ging iets fout'
      if (data.success) {
        setTimeout(function () {
          window.location.href = '/finds?status=in%20bewerking'
        }, 1000)
      }
    },
    submitError ({data}) {
      console.error(data)
      alert('Er trad een fout op')
    },
    post (status) {
      this.submitting = true
      this.status = status
      var f = ''
      for (var i in this.imgRemarks) {
        f += this.imgRemarks[i] ? '\n\nFoto ' + (parseInt(i)+1) + '\n * ' + this.imgRemarks[i].join('\n * ') : ''
      }
      this.remarks = (this.remarks + f).trim()
      var data = {
        objectValidationStatus: status,
        embargo: this.embargo,
        feedback: this.feedback,
        remarks: this.remarks
      }
      console.log('Submitting', JSON.parse(JSON.stringify(data)))
      this.$http.post('/objects/' + this.obj + '/validation', data).then(this.submitSuccess, this.submitError)
    }
  },
  events: {
    imgRemark (index) {
      this.$set('imgRemarks[' + index + ']', [])
    }
  },
  attached () {
    $('.ui.checkbox', this.$el).checkbox()
  },
  components: {
    PhotoValidation,
    TextareaGrowing,
  }
}
</script>