<template>
  <div class="ui form" @submit.prevent="submit" :action="submitAction">
    <h3>Vondst valideren</h3>
    <div class="field">
      <label for="description">Opmerkingen bij validatie</label>
      <textarea-growing id="description" :model.sync="remarks"></textarea-growing>
    </div>
    <photo-validation :model="remarks" :index="index" v-for="(index, remarks) in imgRemarks"></photo-validation>
    <p>
      <button @click="post('gevalideerd')" class="ui green big button" :class="{green:valid}" :disabled="!valid">
        <i class="thumbs up icon"></i> Valideren
      </button>
    </p>
    <p>&nbsp;</p>
    <div class="equal width fields">
      <div class="field">
        <p><button @click="post('revisie nodig')" class="ui button" :class="{yellow:!valid}">Revisie</button>
        <p>De informatie is onvolledig of mogelijk niet correct en moet herzien worden voor publicatie
      </div>
      <div class="field">
        <p><button @click="post('onder embargo')" class="ui button" :class="{orange:!valid}">Embargo</button></p>
        <p>De informatie is gevoelig en mag voorlopig niet gepubliceerd worden
      </div>
      <div class="field">
        <p><button @click="post('verwijderd')" class="ui button" :class="{red:!valid}">Afwijzen</button>
        <p>Dit is geen archeologische vondst
      </div>
    </div>
  </div>
</template>

<script>
import PhotoValidation from './PhotoValidation';
import TextareaGrowing from './TextareaGrowing';

export default {
  props: ['obj'],
  data () {
    return {
      remarks: '',
      imgRemarks: {}
    }
  },
  computed: {
    valid () {
      return !this.remarks && !this.imgRemarks.length
    }
  },
  methods: {
    submitSuccess ({data}) {
      console.log('Validation', data)
      if (data.success) {
        window.location.href = '/finds'
      }
    },
    submitError ({data}) {
      console.error(data)
      alert('Er trad een fout op')
    },
    post (status) {
      var f = ''
      for (var i in this.imgRemarks) {
        f += this.imgRemarks[i] ? '\n\nFoto ' + (parseInt(i)+1) + '\n * ' + this.imgRemarks[i].join('\n * ') : ''
      }
      this.remarks = (this.remarks + f).trim()
      var data = {
        objectValidationStatus: status,
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
  components: {
    PhotoValidation,
    TextareaGrowing,
  }
}
</script>