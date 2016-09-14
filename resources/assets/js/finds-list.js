import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'
import FindsList from './components/FindsList'
import FindsFilter from './components/FindsFilter'
import MapControls from './components/MapControls'
import {load, Map as GoogleMap, Marker, Rectangle, InfoWindow} from 'vue-google-maps'
// import {load, Map, Marker, Circle} from 'vue-google-maps/src/main.js'
import DevBar from './components/DevBar'

import Notifications from './mixins/Notifications'
import {findTitle, inert} from './const.js'

import parseLink from 'parse-link-header'

const HEATMAP_RADIUS = 0.05
const GEO_ROUND = 0.01

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  data () {
    return {
      paging: window.link ? parseLink(window.link) : {},
      finds: window.initialFinds || [],
      filterState: window.filterState || {myfinds: false},
      filterName: '',
      user: window.medeaUser,
      map: {
        type: false,
        info: null,
        center: {lat: 50.9, lng: 4.3},
        zoom: 8
      },
      markerOptions: {
        fillOpacity: 0.1,
        strokeWeight: 1
      },
      rawmap: null,
      loaded: false
    }
  },
  computed: {
    heatmapMax () {
      return this.rawmap ? Math.max.apply(Math, this.rawmap.map(x => x.count)) : 0
    },
    heatmap () {
      var max = this.heatmapMax
      return this.rawmap && this.rawmap.map(x => {
        let co = x.gridCenter.split(',')
        return {
          options: {
            fillOpacity: 0.1 + 0.6 * x.count / max,
            strokeWeight: 0
          },
          bounds: {
            north: parseFloat(co[0]) + HEATMAP_RADIUS,
            south: parseFloat(co[0]) - HEATMAP_RADIUS,
            east: parseFloat(co[1]) + HEATMAP_RADIUS,
            west: parseFloat(co[1]) - HEATMAP_RADIUS
          }
        }
      })
    },
    markerNeeded () {
      return this.map.zoom < 10
    },
    saved () {
      return JSON.parse(this.user.savedSearches || '[]')
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
      var model = inert(this.filterState)
      var type = model.type
      if (model.status == 'gevalideerd') {
        delete model.status
      }
      if (model.name) {
        delete model.name
      }
      if (model.myfinds && this.user.isGuest) {
        delete model.myfinds
      }
      if (model.type) {
        delete model.type
      }
      var query = Object.keys(model).map(function(key, index) {
        return model[key] && model[key] !== '*' ? key + '=' + encodeURIComponent(model[key]) : null
      }).filter(Boolean).join('&')
      query = query ? '/finds?' + query : '/finds?'
      window.history.pushState({}, document.title, query)
      console.log('fetch')
      if (cause !== 'heatmap' && this.query === query) {
        return
      }
      this.query = query
      this.$http.get('/api' + query).then(this.fetchSuccess, function () {
        console.error('could not fetch findevents')
      })
      if (type === 'heatmap') {
        console.log('loading heatmap')
        this.$http.get('/api' + query + '&type=heatmap').then(this.heatmapSuccess, function () {
          console.error('could not fetch heatmap')
        })
      }
    },
    fetchSuccess (res) {
      this.paging = parseLink(res.headers('link'))
      this.finds = res.data
    },
    heatmapSuccess (res) {
      this.rawmap = res.data
    },
    mapToggle (v) {
      if (this.filterState.type === v) {
        this.$set('filterState.type', false)
      } else {
        this.$set('filterState.type', v)
      }
    },
    mapClick (f) {
      this.map.info = f.title
    },
    toggleMyfinds () {
      this.filterState.myfinds = this.filterState.myfinds ? false : 'yes'
      this.fetch()
    },
    sortBy (type) {
      if (this.filterState.order == type) {
        this.filterState.order = '-' + type
      } else if (this.filterState.order == '-' + type) {
        this.filterState.order = false
      } else {
        this.filterState.order = type
      }
      this.fetch()
    },
    persistSearches () {
      // Save the new list of favorites
      this.$http.put('/persons/' + this.user.id, {
        _token: 'PUT',
        id: this.user.id,
        savedSearches: this.user.savedSearches
      })
      .then(function () {
        console.log('Searches saved')
      }).catch(function () {
        console.warn('Something went wrong')
      })
    }
  },
  ready () {
    console.log(JSON.parse(JSON.stringify(window.initialFinds)))
    if (!this.finds || !this.finds.length) {
      this.fetch()
    }
    if (this.filterState.type && !this.loaded) {
      load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k'})
      this.loaded = true
    }
  },
  events: {
    mapFocus (position, accuracy) {
      this.map.center = position
      this.map.zoom = Math.min(14, accuracy ? Math.floor(25 - Math.log2(accuracy)) : 14)
      // nextTick is just to be sure that the map immediately shows the correct location
      this.$nextTick(function () {
        this.$set('filterState.type', 'map')
      })
    },
    saveSearch (name) {
      var saved = this.saved
      saved.push({name: name, state: this.filterState})
      this.user.savedSearches = JSON.stringify(saved)
      this.persistSearches()
    },
    rmSearch () {
      // Remove from saved searches
      this.user.savedSearches = JSON.stringify(this.saved.filter(s => s.name !== this.filterName))
      this.persistSearches()
    }
  },
  filters: {
    markable (finds) {
      return finds
        .filter(f => f.findSpot && f.findSpot.location && f.findSpot.location.lat)
        .map(f => {
          let pubLat = Math.round(f.findSpot.location.lat / GEO_ROUND) * GEO_ROUND
          let pubLng = Math.round(f.findSpot.location.lng / GEO_ROUND) * GEO_ROUND
          return {
          identifier: f.identifier,
          title: findTitle(f),
          accuracy: f.findSpot.location.accuracy || 2000,
          position: {lat: f.findSpot.location.lat, lng: f.findSpot.location.lng},
          bounds: {
            north: pubLat + GEO_ROUND / 2,
            south: pubLat - GEO_ROUND / 2,
            east: pubLng + GEO_ROUND / 2,
            west: pubLng - GEO_ROUND / 2
          }
        }
      })
    }
  },
  watch: {
    'filterState.type' (type) {
      if (type && !this.loaded) {
        load({key:'AIzaSyDCuDwJ-WdLK9ov4BM_9K_xFBJEUOwxE_k'})
        this.loaded = true
      }
      if (type === 'heatmap') {
        this.fetch('heatmap')
      }
    },
    'user': {
      deep: true,
      handler (user) {
        localStorage.debugUser = JSON.stringify(user) 
      }
    }
  },
  mixins: [Notifications],
  components: {
    DevBar,
    FindsFilter,
    FindsList,
    MapControls,
    GoogleMap,
    Marker,
    InfoWindow,
    Rectangle
  }
})

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
