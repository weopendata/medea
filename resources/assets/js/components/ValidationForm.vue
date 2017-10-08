<template>
  <div class="ui form" @submit.prevent :action="submitAction">
    <div class="ui warning message visible" v-if="validation.feedback">
      <p>
        <b>De vondst werd aangepast door de detectorist. Vink aan welke velden ok zijn, indien alles ok is bevonden kan de vondst goedgekeurd worden en is ze gevalideerd. </b>
      </p>
    </div>
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
            <label>Deze vondstfiche bevat gevoelige informatie (plaats onder embargo)</label>
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
    <photo-validation :model="remark" :index="index" v-for="(index, remark) in imgRemarks" v-if="remark!==false"></photo-validation>
    <p v-if="result" v-text="result"></p>
    <p v-if="!remove&&valid">
      <button @click="post('Gepubliceerd')" class="ui green big button" :class="{green:valid}" :disabled="!valid">
        <i class="thumbs up icon"></i> Goedkeuren
      </button>
    </p>
    <p v-if="!remove&&!valid">
      <b>De vondst kan alleen goedgekeurd worden als alle velden aangevinkt zijn.</b>
      <a href="#" @click.prevent="checkAll">Alles aanvinken</a>
    </p>
    <p v-if="!remove&&!valid">
      <button @click="post('Aan te passen')" class="ui orange big button">Terug naar detectorist sturen</button>
    </p>
    <p v-if="remove">
      <button @click="post('Wordt verwijderd')" class="ui red big button">Afwijzen</button>
    </p>
  </div>
</template>

<script>
import PhotoValidation from './PhotoValidation';
import TextareaGrowing from './TextareaGrowing';

import {inert} from '../const.js';

export default {
  props: ['obj', 'feedback', 'json'],
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
    // Get the last validation
    validation () {
      return this.validationList[0] || {
        feedback: {}
      }
    },
    // Order validations: most recent first
    validationList () {
      try {
        return JSON.parse(this.json).sort((a, b) => b.timestamp.localeCompare(a.timestamp))
      } catch (e) {}
      return []
    },
    hasFeedback () {
      return Object.values(this.feedback).filter(Boolean).length > 0
    },
    hasImgRemarks () {
      return Object.values(this.imgRemarks).filter(Boolean).length > 0
    },
    valid () {
      return !this.hasFeedback && !this.hasImgRemarks
    }
  },
  methods: {
    checkAll() {
      for (const key in this.imgRemarks) {
        this.imgRemarks[key] = false
      }
      for (const key in this.feedback) {
        this.feedback[key] = false
      }
    },
    submitSuccess ({data}) {
      this.result = data.success ? 'Status van de vondst: ' + this.status : 'Er ging iets fout'
      if (data.success) {
        setTimeout(function () {
          window.location.href = '/finds?status=Klaar voor validatie'
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

      // Attach extra remarks about the photos
      var f = ''
      for (var i in this.imgRemarks) {
        f += this.imgRemarks[i] ? '\n\nFoto ' + (parseInt(i)+1) + '\n * ' + this.imgRemarks[i].join('\n * ') : ''
      }
      this.remarks = (this.remarks + f).trim()

      // Gather all data
      // TODO: it would be more consistent if the feedback property was calculated in the front-end
      //       that would solve the json_encode issue below
      var data = {
        objectValidationStatus: status,
        embargo: this.embargo,
        feedback: this.feedback,
        imgRemarks: this.imgRemarks,
        remarks: this.remarks
      }
      this.$http.post('/objects/' + this.obj + '/validation', data).then(this.submitSuccess, this.submitError)
    }
  },
  events: {
    imgRemark (index) {
      var remarks = inert(this.imgRemarks)

      // Fix php json_encode issue where objects are encoded as arrays
      if (Array.isArray(remarks)) {
        var oldRemarks = remarks
        remarks = {}
        for (var i = oldRemarks.length - 1; i >= 0; i--) {
          remarks[i] = oldRemarks[i]
        }
      }

      // Toggle the remark list
      if (remarks[index]) {
        delete remarks[index]
      } else {
        remarks[index] = []
      }
      this.imgRemarks = remarks
    }
  },
  attached () {
    $('.ui.checkbox', this.$el).checkbox()

    // Fill in the previous validation feedback
    if (this.json && this.validation && this.validation.objectValidationStatus !== 'Gepubliceerd') {
      this.remarks = this.validation.remarks

      const photograph = this.$parent.find.object.photograph
      const feedback = {}
      const imgRemarks = {}

      // Only load remarks of existing images
      for (var i = 0; i < photograph.length; i++) {
        const id = photograph[i].identifier
        if (this.validation.feedback[id]) {
          feedback[id] = this.validation.feedback[id]
          imgRemarks[id] = this.validation.imgRemarks[id]
        }
      }

      // Also load feedback, but not of images
      for (const key in this.validation.feedback) {
        if (isNaN(parseInt(key))) {
          feedback[key] = this.validation.feedback[key]
        }
      }

      this.feedback = feedback
      this.imgRemarks = imgRemarks
    }
  },
  components: {
    PhotoValidation,
    TextareaGrowing,
  }
}
</script>