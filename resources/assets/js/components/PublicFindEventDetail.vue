<template>
  <article style="max-width:900px;margin:2em auto;">
    <div class="card card-center fe-card">
      <div class="card-textual">
        <h1 class="card-title">{{findTitle}}</h1>
        <div class="ui two columns doubling grid">
          <div class="column" :class="{'fe-validating':validating}">
            <object-features-extended :find="find" :typology="typologyInformation" :excavation="excavation"
                                      :context="context" v-if="find.object"/>
          </div>
          <div class="column scrolling">
            <div class="fe-imglist">
              <div class="fe-img" v-for="(image, index) in find.object.photograph">
                <photoswipe-thumb :image="image" :index="index" @initPhotoswipe="initPhotoswipe"></photoswipe-thumb>
                <i class="magnify icon"></i>
              </div>
            </div>
            <gmap-map v-if="map.center" :center.sync="map.center" :zoom.sync="map.zoom" class="fe-map">
              <gmap-marker :position="markerPosition"></gmap-marker>
            </gmap-map>
          </div>
        </div>
      </div>
      <br/>
      <div id="fb-root"></div>
      <div class="card-textual ui form">
        <div class="fb-share-button"
             :data-href="'/finds/' + find.identifier"
             data-layout="button">
        </div>
      </div>
      <div class="card-textual ui form">
        <div class="field">
          <label>Citeer deze vondstfiche</label>
          <div class="cite" v-text="cite"></div>
        </div>
      </div>
      <div class="card-bar text-right" v-if="editable">
        <a class="btn" :href="'/finds/' + find.identifier + '/edit'">
          <i class="pencil icon"></i>
          Bewerken
        </a>
      </div>
      <div class="card-bar text-right">
        <a :href="'mailto:' + contact + '?Subject=MEDEA vondst ' + find.identifier" target="_top">
          Inhoudelijke fout gevonden op deze pagina? Meld het aan onze beheerder.
        </a>
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

  import {toPublicBounds} from '../const.js'
  import ObjectFeaturesExtended from "./ObjectFeaturesExtended";

  function sameValues(array) {
    return !!array.reduce((a, b) => a === b ? a : NaN)
  }

  export default {
    mounted() {
      this.user = medeaUser || {}
      this.typologyInformation = window.typologyInformation || {}
      this.excavation = window.excavation || {}
      this.context = window.context || {}
    },
    data() {
      return {
        find: null,
        meta: {},
        user: {},
        typologyInformation: {},
        excavation: {},
        context: {},
        loaded: false,
        show: {
          validation: false
        }
      }
    },
    methods: {
      goToFinds() {
        window.location = '/finds'
      },
      fetch() {
        axios.get('/api/finds/' + window.initialFind.identifier)
          .then(response => {
            this.find = response.data
          });
      },
      initPhotoswipe(options) {
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
    computed: {
      map() {
        if (!this.location || !this.location.lat) {
          return {};
        }

        return {
          center: this.location.lat && {lat: parseFloat(this.location.lat), lng: parseFloat(this.location.lng)},
          zoom: 10,
          identifier: this.find.identifier,
          position: {lat: parseFloat(this.location.lat), lng: parseFloat(this.location.lng)}
        }
      },
      findTitle() {
        if (!this.find) {
          return 'De vondst bestaat niet'
        }

        // Add a fallback for when we lack the PAN typology meta-data
        if (!this.typologyInformation) {
          const title = (this.find.object ? [
            this.find.object.objectCategory || 'ongeïdentificeerd',
            this.periodOverruled,
            this.find.object.objectMaterial
          ] : [
            this.find.category || 'ongeïdentificeerd',
            this.periodOverruled,
            this.find.material
          ]).filter(f => f && f !== 'onbekend').join(', ')

          return title + ' (ID-' + this.find.identifier + ')'
        }

        // Build a title based on the typology
        var material = (this.find.object && this.find.object.objectMaterial) ? this.find.object.objectMaterial : this.find.material
        var initialPeriod = 'onbekend';
        var finalPeriod = 'onbekend'

        if (this.typologyInformation.initialPeriod && this.typologyInformation.initialPeriod.label) {
          initialPeriod = this.typologyInformation.initialPeriod.label
        }

        if (this.typologyInformation.finalPeriod && this.typologyInformation.finalPeriod.label) {
          finalPeriod = this.typologyInformation.finalPeriod.label
        }

        var timeFrame = initialPeriod + ' - ' + finalPeriod

        return this.typologyInformation.code + ' (' + this.typologyInformation.label + '), ' + timeFrame + ', ' + material
      },
      periodOverruled() {
        const periods = (this.find.object.productionEvent.productionClassification || [])
          .map(c => c.productionClassificationCulturePeople)
          .filter(Boolean)

        if (periods.length > 1 && !sameValues(periods)) {
          return 'onzeker'
        }

        return periods[0]
      },
      findDetailLink() {
        return window.location.href
      },
      firstImage() {
        if (this.find.object && this.find.object.photograph && this.find.object.photograph.length) {
          return this.find.object.photograph[0].resized
        }
      },
      contact() {
        return window.contact
      },
      location() {
        if (this.excavation && this.excavation.searchArea) {
          return this.excavation.searchArea.location || {}
        }

        return (this.find.findSpot && this.find.findSpot.location) || {}
      },
      markerNeeded() {
        return this.map.zoom < 21 - Math.log2(this.location.accuracy)
      },
      markerPosition() {
        return {
          lat: parseFloat(this.location.lat),
          lng: parseFloat(this.location.lng)
        }
      },
      rectangleBounds() {
        return toPublicBounds(this.location)
      },
      finder() {
        return window.publicUserInfo || {}
      },
      cite() {
        const d = new Date()
        d.setHours(12)
        return (this.finder.name || '')
          + ' (' + this.find.created_at.slice(0, 10) + '). ' +
          this.findTitle +
          '. Geraadpleegd op ' + d.toJSON().slice(0, 10) +
          ' via ' + window.location.href
      },
      showRemarks() {
        return this.find.object.feedback && this.find.object.feedback.length && this.find.object.objectValidationStatus === 'Aan te passen' && this.find.person && this.user.email === this.find.person.email
      },
      validating() {
        return this.user.validator && this.find.object.objectValidationStatus == 'Klaar voor validatie'
      },
      editable() {
        // Finder    if 'Aan te passen' or 'Voorlopige versie'
        // Validator if 'Klaar voor validatie'
        // Admin     always
        var s = this.find.object.objectValidationStatus
        return this.user.email && (this.find.person &&
          (this.user.email === this.find.person.email && ['Aan te passen', 'Voorlopige versie'].indexOf(s) !== -1) ||
          (this.user.validator && s === 'Klaar voor validatie') ||
          this.user.administrator
        )
      }
    },
    created() {
      if (window.initialFind && window.initialFind.identifier) {
        this.find = window.initialFind;
      } else {
        this.fetch()
      }
    },
    components: {
      ObjectFeaturesExtended,
      AddClassification,
      Classification,
      DtCheck,
      ObjectFeatures,
      PhotoswipeThumb,
      ValidationForm,
    }
  }
</script>
