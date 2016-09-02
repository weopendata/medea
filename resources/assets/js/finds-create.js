import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'

import checkbox from 'semantic-ui-css/components/checkbox.min.js'
import extend from 'deep-extend';
import {load, Map, Marker, Circle} from 'vue-google-maps'

import AddClassificationForm from './components/AddClassificationForm'
import DevBar from './components/DevBar'
import DimInput from './components/DimInput.vue'
import FindEvent from './components/FindEvent'
import PhotoUpload from './components/PhotoUpload'
import Step from './components/Step'
import TextareaGrowing from './components/TextareaGrowing'

import Ajax from './mixins/Ajax'
import Notifications from './mixins/Notifications'

import {EMPTY_CLS} from './const.js'

load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k'})

var getCities = function (results) {
  var location = {}, x = 0;
    for (var y = 0, length_2 = results[x].address_components.length; y < length_2; y++) {
      var type = results[x].address_components[y].types[0];
      if (type === "route") {
        location.street = results[x].address_components[y].long_name;
      } else if (type === "locality") {
        location.locality = results[x].address_components[y].long_name;
      } else if (type === "postal_code") {
        location.postalCode = results[x].address_components[y].long_name;
      } else if (type === "street_number") {
        location.number = results[x].address_components[y].long_name;
      }
  }
  return location
}

function fromDimensions (dimensions) {
  var dims = {
    lengte: {unit: 'mm' },
    breedte: {unit: 'mm' },
    diepte: {unit: 'mm' },
    omtrek: {unit: 'mm' },
    diameter: {unit: 'mm' },
    gewicht: {unit: 'g'}
  }
  for (var i = dimensions.length - 1; i >= 0; i--) {
    dims[dimensions[i].dimensionType].value = dimensions[i].measurementValue
    dims[dimensions[i].dimensionType].unit = dimensions[i].dimensionUnit
  }
  return dims
}

function toDimensions (dims) {
  var dimensions = []
  for (let type in dims) {
    if (dims[type].value) {
      dimensions.push({
        dimensionType: type,
        measurementValue: dims[type].value,
        dimensionUnit: dims[type].unit
      })
    }
  }
  return dimensions
}

function fromInscription (ins) {
  return ins && ins.objectInscriptionNote
}
function toInscription (ins) {
  return ins && {
    objectInscriptionNote: ins
  } || undefined
}

function fromTechnique (tech) {
  return tech && tech.productionTechniqueType
}
function toTechnique (tech) {
  return tech && {
    productionTechniqueType: tech
  } || undefined
}

function fromTreatment (tech) {
  return tech && tech.modificationTechnique && tech.modificationTechnique.modificationTechniqueType
}
function toTreatment (tech) {
  return tech && {
    modificationTechnique: {
      modificationTechniqueType: tech
    }
  } || undefined
}

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  data () {
    var initialFind = {
      findDate: new Date().toISOString().slice(0, 10),
      findSpot: {
        findSpotTitle: null,
        findSpotDescription: '',
        findSpotType: '',
        location: {
          address: {
            locationAddressStreet: null,
            locationAddressNumber: null,
            locationAddressLocality: null,
            locationAddressPostalCode: null
          },
          accuracy: 1,
          lat: null,
          lng: null
        }
      },
      object: {
        feedback: null,
        objectValidationStatus: 'in bewerking',
        objectDescription: null,
        objectCategory: 'onbekend',
        objectMaterial: null,
        surfaceTreatment: null,
        period: 'onbekend',
        photograph: [],
        dimensions: [],
        productionEvent: {
          productionClassification: []
        }
      }
    };
    if (window.initialFind) {
      extend(initialFind, window.initialFind)
    }
    return {
      // Location picker
      map: {
        center: {lat: 50.9, lng: 4.3},
        zoom: 8
      },
      marker: {
        visible: false,
        options: {
          fillColor: 'red',
          fillOpacity: 0.4,
          strokeColor: 'red',
          strokeWeight: 1,
          draggable: true,
          editable: true,
        },
        draggable: true,
        clickable: true
      },
      // Dropdowns
      fields: window.fields,
      // Model
      find: initialFind,
      currentStatus: initialFind.object.objectValidationStatus,
      // Mapped to model
      dims: fromDimensions(initialFind.object.dimensions),
      inscription: fromInscription(initialFind.object.objectInscription),
      technique: fromTechnique(initialFind.object.productionEvent.productionTechnique),
      treatment: fromTreatment(initialFind.object.treatmentEvent),
      // Interface state
      today: new Date().toISOString().slice(0, 10),
      show: {
        map: false,
        spotdescription: false,
        place: false,
        address: false,
        locality: false,
        co: false,
        technique: false,
        lengte: true,
        breedte: true,
        diepte: true,
        omtrek: false,
        diameter: false,
        gewicht: false
      },
      // Form state
      ready: [],
      step: initialFind.identifier ? 0 : 1,
      submitAction: window.initialFind ? '/finds/' + window.initialFind.identifier : '/finds',
      redirectTo: window.initialFind ? '/finds/' + window.initialFind.identifier : '/finds?myfinds=yes',
      // App state
      user: window.medeaUser
    }
  },
  computed: {
    latlng: {
      get: function () {
        return {lat: parseFloat(this.find.findSpot.location.lat), lng: parseFloat(this.find.findSpot.location.lng)}
      },
      set: function ({lat, lng}) {
        this.find.findSpot.location.lat = parseFloat(lat.toFixed(6))
        this.find.findSpot.location.lng = parseFloat(lng.toFixed(6))
      }
    },
    accuracy: {
      get: function () {
        return parseInt(this.find.findSpot.location.accuracy)
      },
      set: function (num) {
        this.find.findSpot.location.accuracy = parseInt(parseFloat(num.toPrecision(2))) || 1
      }
    },
    f () {
      return this.validation.feedback
    },
    validation () {
      return this.validationList[0] || {
        feedback: {}
      }
    },
    validationList () {
      return JSON.parse(this.find.object.feedback).sort((a, b) => b.timestamp.localeCompare(a.timestamp)) || []
    },
    hasFeedback () {
      return this.validationList.length > 0
    },
    accuracyStep () {
      return Math.max(1, Math.pow(10, Math.floor(Math.log10(this.find.findSpot.location.accuracy) - 1)))
    },
    markerNeeded () {
      return this.map.zoom < 21 - Math.log2(this.accuracy)
    },
    toValidate () {
      return this.find.object.objectValidationStatus === 'in bewerking'
    },
    submittable () {
      return !this.toValidate || (this.step1valid && this.step2valid && this.step3valid)
    },
    step1valid () {
      return this.hasFindDetails
    },
    hasFindDetails () {
      return this.hasFindSpot && this.find.findDate
    },
    hasFindSpot () {
      return this.find.findSpot.location.lat && this.find.findSpot.location.lng
    },
    hasLocation () {
      return this.find.findSpot.findSpotTitle || this.find.findSpot.location.address.locationAddressLocality || this.find.findSpot.location.address.locationAddressStreet || this.find.findSpot.location.address.line
    },

    step2valid () {
      return this.hasImages
    },
    hasImages () {
      return this.find.object.photograph.length > 1
    },

    step3valid () {
      return this.hasDimensions
    },
    hasDimensions () {
      return this.dims.lengte.value || this.dims.breedte.value || this.dims.diepte.value || this.dims.omtrek.value || this.dims.diameter.value || this.dims.gewicht.value
    }
  },
  methods: {
    toStep (i, show) {
      this.formdata()
      this.step = i
      this.show[show] = true
      var elem = document.getElementById('step' + i)
      if (elem) {
        this.$nextTick(() => elem.scrollIntoView())
      }
    },
    setMarker (event) {
      this.marker.visible = true
      this.latlng = {
        lat: event.latLng.lat(),
        lng: event.latLng.lng()
      }
    },
    changeMarker (event) {
      console.log(event, 'dragged')
    },
    reverseGeocode () {
      var google = window.google
      var self = this
      var a = this.find.findSpot.location.address
      this.geocoder = this.geocoder || new google.maps.Geocoder()
      this.geocoder.geocode({
        location: this.latlng,
        region: 'be'
      }, function (results, status) {
        if (status !== google.maps.GeocoderStatus.OK) {
          return console.warn('reverse geocoding: failed', status)
        } else if (status === google.maps.GeocoderStatus.ZERO_RESULTS) {
          return console.warn('reverse geocoding: no results', status)
        }
        var location = getCities(results)
        self.find.findSpot.location.address.locationAddressStreet = location.street
        self.find.findSpot.location.address.locationAddressNumber = location.number
        self.find.findSpot.location.address.locationAddressLocality = location.locality
        self.find.findSpot.location.address.locationAddressPostalCode = location.postalCode

        // Center map
        self.map.center = {
          lat: results[0].geometry.location.lat(),
          lng: results[0].geometry.location.lng()
        }

        // Calculate approximate accuracy
        var dist = self.haversineDistance(results[0].geometry.viewport.getSouthWest(), results[0].geometry.viewport.getNorthEast())
        dist = parseFloat((dist / 4).toPrecision(1)).toFixed()
        self.map.zoom = Math.floor(24 - Math.log2(dist))

        // Show address
        if (location.street) {
          self.show.address = true
        }
      })
    },
    showOnMap () {
      var google = window.google
      var self = this
      var a = this.find.findSpot.location.address
      this.geocoder = this.geocoder || new google.maps.Geocoder()
      this.geocoder.geocode({
        address: (a.locationAddressStreet ? a.locationAddressStreet + (a.locationAddressNumber || '') + ' , ': '') + (a.locationAddressPostalCode || '') + a.locationAddressLocality,
        region: 'be'
      }, function (results, status) {
        if (status !== google.maps.GeocoderStatus.OK) {
          self.show.map = true
          return console.warn('geocoding failed', status)
        }
        if (status === google.maps.GeocoderStatus.ZERO_RESULTS) {
          self.show.map = true
          return console.warn('no results', status)
        }
        var location = getCities(results)
        self.find.findSpot.location.address.locationAddressStreet = location.street
        self.find.findSpot.location.address.locationAddressLocality = location.locality
        self.find.findSpot.location.address.locationAddressPostalCode = location.postalCode

        self.marker.visible = true
        self.latlng = self.map.center = {
          lat: results[0].geometry.location.lat(),
          lng: results[0].geometry.location.lng()
        }
        // Calculate approximate accuracy
        var dist = self.haversineDistance(results[0].geometry.viewport.getSouthWest(), results[0].geometry.viewport.getNorthEast())
        dist = parseFloat((dist / 4).toPrecision(1)).toFixed()
        self.map.zoom = Math.floor(24 - Math.log2(dist))
        // self.find.findSpot.location.accuracy = dist
        self.show.map = true

        // Show address
        if (location.street) {
          self.show.address = true
        }
        self.$nextTick(function(){
          document.querySelector('#location-picker').scrollIntoView()
        })
      })
    },
    haversineDistance (p1, p2) {
      var rad = function(x) {
        return x * Math.PI / 180;
      }
      var R = 6378137; // Earth’s mean radius in meter
      var dLat = rad(p2.lat() - p1.lat());
      var dLong = rad(p2.lng() - p1.lng());
      var a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos(rad(p1.lat())) * Math.cos(rad(p2.lat())) * Math.sin(dLong / 2) * Math.sin(dLong / 2);
      var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      var d = R * c;
      return d; // returns the distance in meter
    },
    pushCls () {
      this.find.object.productionEvent.productionClassification.push(EMPTY_CLS)
    },
    formdata () {
      // Dimensions
      this.find.object.dimensions = toDimensions(this.dims)
      this.find.object.objectInscription = toInscription(this.inscription)
      this.find.object.productionEvent.productionTechnique = toTechnique(this.technique)
      this.find.object.treatmentEvent = toTreatment(this.treatment)

      console.log(JSON.parse(JSON.stringify(this.find)))
      return this.find
    },
    submitSuccess (res) {
      const newStatus = this.find.object.objectValidationStatus
      var eventName = this.find.identifier ? 'Update' : 'Create'
      if (newStatus === this.currentStatus) {

      } else if (newStatus === 'in bewerking') {
        eventName += 'AndSubmit'
      } else if (newStatus === 'revisie nodig') {
        eventName += 'ButNotSubmit'
      } else {
        eventName += 'ButUnexpectedStatus'
      }
      _paq.push(['trackEvent', 'FindEvent', eventName, res.data.id || 0]);
      window.location.href = res.data.url || this.redirectTo
    }
  },
  ready () {
    if (window.initialFind) {
      if (this.latlng.lat) {
        this.map.center = this.latlng
        this.show.map = true
        this.marker.visible = true
      }
    }
    $('.ui.checkbox', this.$el).checkbox()
    // $('.step .ui.dropdown').dropdown()
  },
  watch: {
    'find.object.objectCategory' (val) {
      if (val == 'munt' || val == 'rekenpenning') {
        this.show.diameter = true
        this.show.lengte = false
        this.show.breedte = false
      }
    },
    'user': {
      deep: true,
      handler (user) {
        localStorage.debugUser = JSON.stringify(user)
      }
    }
  },
  el: 'body',
  mixins: [Ajax, Notifications],
  components: {
    DevBar,
    Step,
    Map,
    Marker,
    Circle,
    PhotoUpload,
    DimInput,
    FindEvent,
    TextareaGrowing,
    AddClassificationForm
  }
});

window.startIntro = function () {
  introJs()
  .setOptions({
    scrollPadding: 200
  })
  .setOption('hideNext', true)
  .setOption('hidePrev', true)
  .setOption('doneLabel', 'Ik heb alles begrepen!')
  .setOption('skipLabel', 'Ik heb alles begrepen!')
  .start()
}
if (window.location.href.indexOf('startIntro') !== -1) {
  window.startIntro()
}
