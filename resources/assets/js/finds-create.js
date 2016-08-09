import Vue from 'vue/dist/vue.min.js';
import VueResource from 'vue-resource/dist/vue-resource.min.js';
import DevBar from './components/DevBar';
import Step from './components/Step';
import checkbox from 'semantic-ui-css/components/checkbox.min.js';

import {load, Map, Marker, Circle} from 'vue-google-maps';

import PhotoUpload from './components/PhotoUpload';
import DimInput from './components/DimInput.vue';
import FindEvent from './components/FindEvent';
import AddClassificationForm from './components/AddClassificationForm'
import Ajax from './mixins/Ajax';
import extend from 'deep-extend';

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
            street: null,
            number: null,
            locality: null,
            postalCode: null
          },
          accuracy: 1,
          lat: null,
          lng: null
        }
      },
      object: {
        objectValidationStatus: 'in bewerking',
        objectDescription: null,
        objectCategory: 'onbekend',
        objectMaterial: null,
        surfaceTreatment: null,
        treatmentEvent: {
          modificationTechnique: {
            modificationTechniqueType: null
          }
        },
        period: 'onbekend',
        photograph: [],
        dimensions: [],
        productionEvent: {
          productionClassification: [],
          productionTechnique: {
            type: null
          }
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
      // Mapped to model
      toValidate: true,
      inscription: null,
      dims: {
        lengte: {dimensionUnit: 'cm' },
        breedte: {dimensionUnit: 'cm' },
        diepte: {dimensionUnit: 'cm' },
        omtrek: {dimensionUnit: 'cm' },
        diameter: {dimensionUnit: 'cm' },
        gewicht: {dimensionUnit: 'g'}
      },
      // Interface state
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
      step: 1,
      submitAction: window.initialFind ? '/finds/' + window.initialFind.identifier : '/finds',
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
    accuracyStep () {
      return Math.max(1, Math.pow(10, Math.floor(Math.log10(this.find.findSpot.location.accuracy) - 1)))
    },
    markerNeeded () {
      return this.map.zoom < 21 - Math.log2(this.accuracy)
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
      return this.find.findSpot.location.locationPlaceName.appellation || this.find.findSpot.location.address.locality || this.find.findSpot.location.address.street || this.find.findSpot.location.address.line
    },

    step2valid () {
      return this.hasImages
    },
    hasImages () {
      return this.find.object.photograph.length
    },

    step3valid () {
      return this.hasDimensions
    },
    hasDimensions () {
      return this.dims.lengte.value || this.dims.breedte.value || this.dims.diepte.value || this.dims.omtrek.value || this.dims.diameter.value || this.dims.gewicht.value
    }
  },
  methods: {
    toStep (i) {
      this.formdata()
      this.step = i
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
        self.find.findSpot.location.address.street = location.street
        self.find.findSpot.location.address.number = location.number
        self.find.findSpot.location.address.locality = location.locality
        self.find.findSpot.location.address.postalCode = location.postalCode

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
        address: (a.street ? a.street + (a.number || '') + ' , ': '') + (a.zip || '') + a.locality,
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
        self.find.findSpot.location.address.street = location.street
        self.find.findSpot.location.address.locality = location.locality
        self.find.findSpot.location.address.postalCode = location.postalCode

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
      this.find.object.productionEvent.productionClassification.push({
        type: '',
        period: '',
        nation: '',
        startDate: '',
        endDate: '',
        publication: [{publicationTitle: ''}],
        description: ''
      })
    },
    import () {
      // Inverse of formdata()
      // Dimensions
      for (var i = 0; i < this.find.object.dimensions.length; i++) {
        this.dims[this.find.object.dimensions[i].dimensionType] = {
          measurementValue: this.find.object.dimensions[i].measurementValue,
          dimensionUnit: this.find.object.dimensions[i].dimensionUnit
        }
      }
      // Inscription
      if (this.find.object.objectInscription) {
        this.$set('inscription', this.find.object.objectInscription.objectInscriptionNote)
      }
    },
    formdata () {
      // Dimensions
      var dimensions = []
      for (let type in this.dims) {
        if (this.dims[type].measurementValue) {
          dimensions.push({
            dimensionType: type,
            measurementValue: this.dims[type].measurementValue,
            dimensionUnit: this.dims[type].dimensionUnit
          })
        }
      }
      this.find.object.dimensions = dimensions

      // Inscription
      if (this.inscription) {
        this.find.object.objectInscription = {
          objectInscriptionNote: this.inscription
        }
      }

      // Validation status
      this.find.object.objectValidationStatus = this.toValidate ? 'in bewerking' : 'revisie nodig'
      return this.find
    },
    submitSuccess () {
      window.location.href = this.submitAction
    }
  },
  ready () {
    if (window.initialFind) {
      if (this.latlng.lat) {
        this.map.center = this.latlng
        this.show.map = true
        this.marker.visible = true
      }
      this.import()
    }
    $('.ui.checkbox').dropdown()
    $('.step .ui.dropdown').dropdown()
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
  mixins: [Ajax],
  components: {
    DevBar,
    Step,
    Map,
    Marker,
    Circle,
    PhotoUpload,
    DimInput,
    FindEvent,
    AddClassificationForm
  }
});
