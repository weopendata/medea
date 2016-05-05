import Vue from 'vue/dist/vue.min.js';
import VueResource from 'vue-resource/dist/vue-resource.min.js';
import FindsList from './components/FindsList';
import FindsFilter from './components/FindsFilter';
import MapControls from './components/MapControls';
import {load, Map, Marker, Circle} from 'vue-google-maps';
// import {load, Map, Marker, Circle} from 'vue-google-maps/src/main.js';
import DevBar from './components/DevBar';

import parseLink from 'parse-link-header';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    DevBar,
    FindsFilter,
    FindsList,
    MapControls,
    Map,
    Marker,
    Circle
  },
  data () {
    return {
      paging: window.link ? parseLink(window.link) : {},
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
      loaded: false
    }
  },
  ready () {
    console.log(JSON.parse(JSON.stringify(window.initialFinds)))
    if (!this.finds || !this.finds.length) {
      this.fetch()
    }
    if (this.filterState.showmap && !this.loaded) {
      load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k'})
      this.loaded = true
    }
  },
  methods: {
    relevant (find) {
      if (find.object.objectValidationStatus == 'in bewerking') {
        if (!this.user.validator) {
          console.warn('Security error, this user is not allowed to see this find')
        }
      } else if (find.object.objectValidationStatus != 'gevalideerd' && !this.user.administrator) {
        console.warn('Security error, this user is not allowed to see this find')
      }
      return true
    },
    fetch (cause) {
      var model = this.filterState
      if (model.status == 'gevalideerd') {
        delete model.status
      }
      if (model.name) {
        delete model.name
      }
      if (model.myfinds && this.user.isGuest) {
        delete model.myfinds
      }
      var query = Object.keys(model).map(function(key, index) {
        return model[key] && model[key] !== '*' ? key + '=' + encodeURIComponent(model[key]) : null;
      }).filter(Boolean).join('&');
      query = query ? '/finds?' + query : '/finds'
      window.history.pushState({}, document.title, query)
      if (cause == 'showmap' || this.query == query) {
        return
      }
      this.query = query
      this.$http.get('/api' + query).then(this.fetchSuccess, function () {
        console.error('could not fetch findevents')
      });
    },
    fetchSuccess (res) {
      this.paging = parseLink(res.headers('link'))
      this.finds = res.data
    },
    mapshow (value) {
      this.filterState.showmap = value
      this.fetch('showmap')
    },
    showCity (value) {
      this.filterState.showmap = value
      this.fetch('showmap')
    }
  },
  events: {
    mapFocus (position, accuracy) {
      this.map.center = position
      this.map.zoom = accuracy ? Math.floor(25 - Math.log2(accuracy)) : 18
      // nextTick is just to be sure that the map immediately shows the correct location
      this.$nextTick(function () {
        this.filterState.showmap = true
      })
    }
  },
  filters: {
    markable (finds) {
      return finds
        .filter(f => f.findSpot && f.findSpot.location && f.findSpot.location.lat)
        .map(f => ({
          accuracy: f.findSpot.location.accuracy || 1,
          position: {lat: f.findSpot.location.lat, lng: f.findSpot.location.lng}
        }))
    }
  },
  watch: {
    'filterState.showmap' (shown) {
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