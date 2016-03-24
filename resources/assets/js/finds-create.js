import Vue from 'vue/dist/vue.min.js';
import VueResource from 'vue-resource/dist/vue-resource.min.js';
import DevBar from './components/DevBar';
import Step from './components/Step';
import checkbox from 'semantic-ui-css/components/checkbox.min.js';
import dropdown from 'semantic-ui-css/components/dropdown.min.js';
import transition from 'semantic-ui-css/components/transition.min.js';

import {load, loaded} from 'vue-google-maps/src/manager.js';
import Map from 'vue-google-maps/src/components/map.vue';
import Marker from 'vue-google-maps/src/components/marker.vue';
import PlaceInput from 'vue-google-maps/src/components/PlaceInput.vue';

import PhotoUpload from './components/PhotoUpload';
import DimInput from './components/DimInput.vue';
import FindEvent from './components/FindEvent';
import AddClassificationForm from './components/AddClassificationForm'
import Ajax from './mixins/Ajax';

load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k', libraries:'places'})

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  data () {
    return {
      centerStart: {lat: 50.9, lng: 4.3},
      marker: {
        visible: false,
        position: {lat: 50.9, lng: 4.3},
        draggable: true,
        clickable: true
      },
      find: {
        toValidate: true,
        finderName: '',
        findDate: (new Date()).toJSON().substr(0, 10),
        "findSpot": {
          "type": "akkerland",
          "title": "een title van de vindplaats",
          "description": "",
          "location": {
            "locationPlaceName": {
              "appellation": "",
              "type": "TBD"
            },
            "address": {
              "street": "",
              "number": "",
              "locality": "",
              "postalCode": ""
            },
            "lat": "",
            "lng": ""
          }
        },
        object: {
          "description" : "",
          "inscription" : "",
          category: "",
          "material" : "",
          "technique" : "",
          "bibliography" : "http://paperonacientgreek.com",
          images: [],
          dimensions: []
        }
      },
      dimensionText: '',
      dims: {
        lengte: {unit: 'cm' },
        breedte: {unit: 'cm' },
        diepte: {unit: 'cm' },
        omtrek: {unit: 'cm' },
        diameter: {unit: 'cm' },
        gewicht: {unit: 'g'}
      },
      show: {
        map: false,
        place: false,
        address: false,
        locality: false,
        co: false,
        lengte: false,
        breedte: false,
        diepte: false,
        omtrek: false,
        diameter: false,
        gewicht: false
      },
      step: 1,
      submitAction: '/finds',
      user: window.medeaUser
    }
  },
  computed: {
    submittable () {
      return this.step1valid && this.step2valid && this.step==3
    },
    step1valid () {
      return this.hasFindDetails
    },
    hasFindDetails () {
      return this.hasFindSpot && this.find.finderName && this.find.findDate
    },
    hasFindSpot () {
      return this.hasLocation && this.find.findSpot.description
    },
    hasLocation () {
      return (this.find.findSpot.location.lat && this.find.findSpot.location.lng) || this.find.findSpot.location.locationPlaceName.appellation || this.find.findSpot.location.address.locality || this.find.findSpot.location.address.street || this.find.findSpot.location.address.line
    },

    step2valid () {
      return this.hasImages && this.find.object.description && this.hasDimensions
    },
    hasImages () {
      return this.find.object.images.length
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
      this.marker.position.lat = event.latLng.lat()
      this.marker.position.lng = event.latLng.lng()
      this.find.findSpot.location.lat = event.latLng.lat()
      this.find.findSpot.location.lng = event.latLng.lng()
    },
    changeMarker (event) {
      console.log(event, 'dragged')
    },
    formdata () {
      this.find.object.dimensions = []
      for (let type in this.dims) {
        if (this.dims[type].value) {
          this.find.object.dimensions.push({
            type: type,
            value: this.dims[type].value,
            unit: this.dims[type].unit
          })
        }
      }
      return this.find
    },
    submitSuccess () {
      window.location.href = '/finds'
    }
  },
  ready () {
    $('.ui.checkbox').checkbox()
    $('.ui.dropdown').dropdown()
    this.categoryMap = window.categoryMap
  },
  watch: {
    'find.object.category' (val) {
      console.log(val, this.categoryMap)
      if (val in this.categoryMap) {
        var dims = this.categoryMap[val]
        for (var i = 0; i < dims.length; i++) {
          this.show[dims[i]] = true;
        }
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
    PlaceInput,
    Marker,
    PhotoUpload,
    DimInput,
    FindEvent,
    AddClassificationForm
  }
});
