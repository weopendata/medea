<template>
  <article style="max-width:900px;margin:2em auto;">
    <div class="ui warning message visible" v-if="showRemarks">
      <p>
        De validator heeft enkele opmerkingen bij validatie.
      </p>
      <p><a class="ui orange button" href="/finds/{{find.identifier}}/edit">Vondst bewerken</a></p>
    </div>
    <div class="ui warning message visible" v-if="find.object.objectValidationStatus == 'Voorlopige versie'">
      <p>
        Dit is een voorlopige versie
      </p>
      <p><a class="ui orange button" href="/finds/{{find.identifier}}/edit">Vondst bewerken</a></p>
    </div>
    <div class="card card-center fe-card">
      <div class="card-textual">
        <h1 class="card-title">{{findTitle}}</h1>
        <div class="ui two columns doubling grid">
          <div class="column" :class="{'fe-validating':validating}">
            <object-features :find="find" :feedback.sync="feedback" :validating="validating"></object-features>
          </div>
          <div class="column scrolling" :class="{'fe-validating':validating}">
            <div class="fe-imglist">
              <div class="fe-img" v-for="image in find.object.photograph">
                <dt-check v-if="validating" :prop="image.identifier" @change="imgRemark($index)"></dt-check>
                <photoswipe-thumb :image="image" :index="$index"></photoswipe-thumb>
                <i class="magnify icon"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-textual ui form">
        <div class="field">
          <label>Citeer deze vondstfiche</label>
          <input type="text" :value="cite" readonly @click="selectThis">
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
            <validation-form :obj="find.object.identifier" :feedback.sync="feedback" :json="find.object.feedback"></validation-form>
            <p>&nbsp;</p>
          </div>
          <div v-if="find.object.objectValidationStatus == 'Gepubliceerd'">
            <div style="max-width: 900px;margin: 0 auto 1em;">
              <h2>
                Classificaties
                <small v-if="find.object.productionEvent.productionClassification">Deze vondst werd {{find.object.productionEvent.productionClassification.length}} keer geclassicifeerd.</small>
              </h2>
            </div>
            <classification v-for="cls in find.object.productionEvent.productionClassification" :cls="cls" :obj="find.object.identifier"></classification>
            <div class="ui orange message" v-if="!find.object.productionEvent||!find.object.productionEvent.productionClassification||!find.object.productionEvent.productionClassification.length">
              <div class="ui header">Deze vondst is niet geclassificeerd</div>
              <p v-if="user.vondstexpert">Voeg jij een classificatie toe?</p>
            </div>
            <add-classification :object="find.object" v-if="user.vondstexpert"></add-classification>
            <p>&nbsp;</p>
          </div>
          <h1 v-if="!user.validator&&find.object.objectValidationStatus !== 'Gepubliceerd' && (user.email!==find.person.email)">
            Security error #20984
            <p>&nbsp;</p>
          </h1>
          <div v-if="find.object.objectValidationStatus == 'Afgeschermd'">
            Deze vondstfiche staat onder embargo.
            <p>&nbsp;</p>
          </div>
          <div v-if="(user.email==find.person.email)">
            <h1 v-if="find.object.objectValidationStatus == 'Klaar voor validatie'" class="status-lg">
              Je vondstfiche wordt gevalideerd.
              <small>Je krijgt een notificatie wanneer de validator de vonstfiche beoordeeld heeft.</small>
              <p>&nbsp;</p>
            </h1>
          </div>
          <div v-else>
            <div v-if="find.object.objectValidationStatus == 'Klaar voor validatie'&&!user.validator">
              Deze vondstfiche wordt gevalideerd.
              <p>&nbsp;</p>
            </div>
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
    finder () {
      return window.publicUserInfo
    },
    cite () {
      const d = new Date()
      d.setHours(12)
      return (this.finder.name || '')
        + ' (' + this.find.created_at.slice(0, 10) + '). ' +
        this.findTitle +
        '. Geraadpleegd op ' + d.toJSON().slice(0, 10) + 
        ' via http://vondsten.be/id/' + this.find.identifier
    },
    showRemarks () {
      return this.find.object.feedback && this.find.object.feedback.length && this.find.object.objectValidationStatus === 'Aan te passen' && this.user.email === this.find.person.email
    },
    validating () {
      return this.user.validator && this.find.object.objectValidationStatus == 'Klaar voor validatie'
    },
    editable () {
      // Finder    if 'Aan te passen' or 'Voorlopige versie'
      // Validator if 'Klaar voor validatie'
      // Admin     always
      var s = this.find.object.objectValidationStatus
      return this.user.email && (
        (this.user.email === this.find.person.email && ['Aan te passen', 'Voorlopige versie'].indexOf(s) !== -1) ||
        (this.user.validator && s === 'Klaar voor validatie') ||
        this.user.administrator
      )
    },
    findTitle () {
      // Not showing undefined and onbekend in title
      var title = [
        this.find.object.objectCategory,
        this.find.object.period,
        this.find.object.objectMaterial
      ].filter(f => f && f !== 'onbekend').join(', ')

      title += ' (ID-' + this.find.identifier + ')'

      return title;
    }
  },
  methods: {
    imgRemark (index) {
      this.$broadcast('imgRemark', index)
    },
    selectThis (evt) {
      if (evt && evt.target) {
        evt.target.select()
      }
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