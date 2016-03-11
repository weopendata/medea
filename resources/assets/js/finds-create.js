import Vue from 'Vue';
import VueResource from 'vue-resource';
import TopNav from './components/TopNav';
import AjaxForm from './components/AjaxForm';
import Step from './components/Step';
import dropdown from 'semantic-ui-css/components/dropdown.min.js';
import transition from 'semantic-ui-css/components/transition.min.js';

import {load, loaded} from 'vue-google-maps/src/manager.js';
import Map from 'vue-google-maps/src/components/map.vue';
import Marker from 'vue-google-maps/src/components/marker.vue';
import PlaceInput from 'vue-google-maps/src/components/PlaceInput.vue';

import PhotoUpload from './components/PhotoUpload';

load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k', libraries:'places'})

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    TopNav,
    AjaxForm,
    Step,
    Map,
    PlaceInput,
    Marker,
    PhotoUpload
  },
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
        finderName: '',
        findDate: (new Date()).toJSON().substr(0, 10),
        "findSpot": {
          "type": "akkerland",
          "title": "een title van de vindplaats",
          "description": "Braak liggend akkerland",
          "location": {
            "locationPlaceName": {
              "appellation": "Bachtn de kuppe",
              "type": "TBD"
            },
            "address": {
              "street": "Akkerlandstraat",
              "number": "3",
              "locality": "Ieper",
              "postalCode": "8900"
            },
            "latlng": {},
            "lat": "4.3487800",
            "lng": "50.8504500"
          }
        },

        object: {
          "description" : "Een speer uit de griekse tijd.",
          "inscription" : "Let the die be cast.",
          "material" : "ijzer",
          "technique" : "other",
          "bibliography" : "http://paperonacientgreek.com",
          images: [],
          dimensions: []
        }
      },
      dimensionText: '',
      step: 1,
      user: window.medeaUser
    }
  },
  computed: {
    step1valid () {
      return this.find.findSpot.location.description && this.find.findSpot.location.address.street
    },
    step2valid () {
      return this.find.findSpot.location.description && this.find.findSpot.location.address.street
    }
  },
  methods: {
    toStep (i) {
      this.step = i
    },
    setMarker (event) {
      this.marker.visible = true
      this.find.findSpot.location.latlng.lat = event.latLng.lat()
      this.find.findSpot.location.latlng.lng = event.latLng.lng()
    }
  },
  ready () {
    $('.ui.dropdown').dropdown();
  },
  watch: {
    'dimensionText' (val) {
      this.find.object.dimensions = val.split('\n').map(function (v) {
        var arr = /([a-zA-Z]*)[:,.\s]*(\d+)\s*([a-zA-Z]*)/.exec(v)
        return {
          type: arr && arr[1],
          value: arr && arr[2],
          unit: arr && arr[3],
        }
      })
    },
    'user': {
      deep: true,
      handler (user) {
        localStorage.debugUser = JSON.stringify(user) 
      }
    }
  }
});
