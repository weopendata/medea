<template>
  <div class="ui form" @submit.prevent="submit" :action="submitAction">
    <h3>Vondst valideren</h3>
    <div class="field">
      <label for="description">Opmerkingen bij validatie</label>
      <textarea-growing id="description" :model.sync="remarks"></textarea-growing>
    </div>
    <photo-validation :model="photos"></photo-validation>
    <p>
      <button @click="post('gevalideerd')" class="ui green big button" :class="{green:valid}" :disabled="!valid">Publiceren</button>
    </p>
    <p>&nbsp;</p>
    <div class="equal width fields">
      <div class="field">
        <p><button @click="post('in bewerking')" class="ui button" :class="{yellow:!valid}">Revisie</button>
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
      photos: []
    }
  },
  computed: {
    valid () {
      return !this.remarks && !this.photos.length
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
      var data = {
        objectValidationStatus: status,
        remarks: (this.remarks + (this.photos.length ? '\n\n# Feedback op foto\'s\n* ' + this.photos.join('\n* ') : '')).trim()
      }
      console.log('Submitting', JSON.parse(JSON.stringify(data)))
      this.$http.post('/objects/' + this.obj + '/validation', data).then(this.submitSuccess, this.submitError)
    }
  },
  components: {
    PhotoValidation,
    TextareaGrowing,
  }
}
</script>