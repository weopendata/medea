import Vue from 'vue/dist/vue.min.js';
import VueResource from 'vue-resource/dist/vue-resource.min.js';
import FindsList from './components/FindsList';
import FindsFilter from './components/FindsFilter';
import {load, Map, Marker, Circle} from 'vue-google-maps';
// import {load, Map, Marker, Circle} from 'vue-google-maps/src/main.js';
import DevBar from './components/DevBar';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    DevBar,
    FindsFilter,
    FindsList,
    Map,
    Marker,
    Circle
  },
  data () {
    return {
      finds: window.initialFinds || [],
      filterState: window.filterState || {myfinds: false},
      user: window.medeaUser,
      map: {
        center: {lat: 50.9, lng: 4.3},
        zoom: 8
      },
      markerOptions: {
        fillColor: '#FF6600',
        fillOpacity: 0.3,
        strokeWeight: 0 
      },
      showmap: false,
      loaded: false
    }
  },
  ready () {
    console.log(JSON.parse(JSON.stringify(window.initialFinds)))
    if (!this.finds || !this.finds.length) {
      this.fetch()
    }
    if (this.showmap && !this.loaded) {
      load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k'})
      this.loaded = true
    }
  },
  methods: {
    fetch (query) {
      query = query || ''
      this.$http.get('/api/finds?' + query).then(function (res) {
        this.finds = res.data
        window.history.pushState({}, document.title, '?' + query)
      }, function () {
        console.error('could not fetch findevents')
      });
    }
  },
  events: {
    mapFocus (position, accuracy) {
      this.map.center = position
      this.map.zoom = accuracy ? Math.floor(25 - Math.log2(accuracy)) : 18
      // nextTick is just to be sure that the map immediately shows the correct location
      this.$nextTick(function () {
        this.showmap = true
      })
    }
  },
  filters: {
    markable (finds) {
      return finds
        .filter(f => f.findSpot && f.findSpot.location && f.findSpot.location.lat)
        .map(f => ({
          accuracy: f.findSpot.location.accuracy || 100,
          position: {lat: f.findSpot.location.lat, lng: f.findSpot.location.lng}
        }))
    }
  },
  watch: {
    'showmap' (shown) {
      if (shown && !this.loaded) {
        load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k'})
        this.loaded = true
      }
    },
    'user': {
      deep: true,
      handler (user) {
        localStorage.debugUser = JSON.stringify(user) 
      }
    }
  }
});