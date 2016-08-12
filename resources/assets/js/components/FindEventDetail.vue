<template>
  <article>
    <div class="card card-center fe-card">
      <div class="card-textual">
        <h1 class="card-title">#{{find.identifier}} {{find.object.objectCategory}} {{find.object.period}} {{find.object.objectMaterial}}</h1>
        <div class="ui two columns doubling grid">
          <div class="column" :class="{'fe-validating':validating}">
            <object-features :find="find" detail="all" :feedback="feedback" :validating="validating"></object-features>
          </div>
          <div class="column" :class="{'fe-validating':validating}">
            <div class="fe-header">
              <div class="fe-imglist">
                <div class="img" v-for="image in find.object.photograph">
                  <dt-check v-if="validating" :prop="image.identifier" @click="imgRemark($index)"></dt-check>
                  <photoswipe-thumb :image="image" :index="$index"></photoswipe-thumb>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-bar text-right" v-if="editable">
        <a class="btn" href="/finds/{{find.identifier}}/edit">
          <i class="pencil icon"></i>
          Bewerken
        </a>
      </div>
    </div>
    <br>
    <div class="wrapper-white-disabled">
      <p>&nbsp;</p>
      <div class="ui container">
          <div v-if="validating">
            <validation-form :obj="find.object.identifier" :feedback="feedback"></validation-form>
            <p>&nbsp;</p>
          </div>
          <div v-if="find.object.objectValidationStatus == 'gevalideerd'">
            <classification v-for="cls in find.object.productionEvent.productionClassification" :cls="cls" :obj="find.object.identifier"></classification>
            <div class="ui orange message" v-if="!find.object.productionEvent&&!find.object.productionEvent.productionClassification&&!find.object.productionEvent.productionClassification.length">
              <div class="ui header">Deze vondst is niet geclassificeerd</div>
              <p v-if="user.vondstexpert">Voeg jij een classificatie toe?</p>
            </div>
            <add-classification :object="find.object" v-if="user.vondstexpert"></add-classification>
            <p>&nbsp;</p>
          </div>
          <h1 v-if="!user.validator&&find.object.objectValidationStatus !== 'gevalideerd' && (user.email!==find.person.email)">
            Security error #20984
            <p>&nbsp;</p>
          </h1>
          <div v-if="find.object.objectValidationStatus == 'embargo'">
            Deze vondst is onder embargo.
            <p>&nbsp;</p>
          </div>
          <div v-if="(user.email==find.person.email)">
            <h1 v-if="find.object.objectValidationStatus == 'in bewerking'" class="status-lg">
              Je vondstfiche wordt gevalideerd.
              <small>Je krijgt een notificatie wanneer de validator de vonstfiche beoordeeld heeft.</small>
              <p>&nbsp;</p>
            </h1>
            <div v-if="find.object.objectValidationStatus == 'revisie nodig'">
              Ofwel is dit een draft, ofwel is er feedback die wijzigingen aan deze vondstfiche gebieden.
              <p>&nbsp;</p>
            </div>
          </div>
          <div v-else>
            <div v-if="find.object.objectValidationStatus == 'in bewerking'">
              Deze vondstfiche wordt gevalideerd.
              <p>&nbsp;</p>
            </div>
            <div v-if="find.object.objectValidationStatus == 'revisie nodig'">
              Deze vondstfiche is in revisie.
              <p>&nbsp;</p>
            </div>
          </div>
          <div v-if="find.object.objectValidationStatus == 'afgekeurd'">
            Deze vondstfiche is niet geschikt voor MEDEA.
          </div>
      </div>
    </div>
  </article>
</template>

<script>
import checkbox from 'semantic-ui-css/components/checkbox.min.js'

import AddClassification from './AddClassification'
import Classification from './Classification'
import DtCheck from './DtCheck'
import ObjectFeatures from './ObjectFeatures'
import PhotoswipeThumb from './PhotoswipeThumb'
import ValidationForm from './ValidationForm'

export default {
  props: ['user', 'find'],
  data () {
    return {
      feedback: {},
      show: {
        validation: false
      }
    }
  },
  computed: {
    validating () {
      return this.user.validator && this.find.object.objectValidationStatus == 'in bewerking'
    },
    editable () {
      return this.user.email === this.find.person.email || (this.user.validator && this.find.object.objectValidationStatus == 'in bewerking')
    }
  },
  methods: {
    imgRemark (index) {
      this.$broadcast('imgRemark', index)
    }
  },
  events: {
    initPhotoswipe (options) {
      if (!window.PhotoSwipe) {
        return console.warn('PhotoSwipe missing')
      }
      var pswpElement = document.querySelector('.pswp');
      var items = this.find.object.photograph.map(img => {
        return {
          src: img.src,
          msrc: img.resized,
          w: img.width || 1600,
          h: img.height || 900
        }
      })
      var gallery = new window.PhotoSwipe(pswpElement, window.PhotoSwipeUI_Default, items, options);
      gallery.init();
    }
  },
  components: {
    AddClassification,
    Classification,
    DtCheck,
    ObjectFeatures,
    PhotoswipeThumb,
    ValidationForm,
  }
}
</script>