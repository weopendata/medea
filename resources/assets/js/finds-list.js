import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'
import FindsList from './components/FindsList'
import FindsFilter from './components/FindsFilter'
import MapControls from './components/MapControls'
import {load, Map as GoogleMap, Marker, Rectangle, InfoWindow} from 'vue-google-maps'
// import {load, Map, Marker, Circle} from 'vue-google-maps/src/main.js'
import DevBar from './components/DevBar'

import Notifications from './mixins/Notifications'
import { inert, toPublicBounds } from './const.js'

import parseLink from 'parse-link-header'

const HEATMAP_RADIUS = 0.05

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
    findTitle (find) {
      // Not showing undefined and onbekend in title
      var title = [
      find.category,
      find.period,
      find.material
      ].filter(f => f && f !== 'onbekend').join(', ')

      return title + ' (ID-' + find.identifier + ')'
    },
    relevant (find) {
      if (find.validation == 'in bewerking') {
        if (!this.user.validator) {
          console.warn('List: Security error, this user is not allowed to see this find')
        }
      } else if (find.validation != 'gevalideerd' && !this.user.administrator) {
        console.warn('List: Security error, this user is not allowed to see this find')
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

      // Do not fetch same query
      if (cause !== 'heatmap' && this.query === query) {
        return
      }

      // Do not push state on first load
      if (!this.query) {
        this.query = query
        return      
      }
      this.query = query
      window.history.pushState({}, document.title, query)

      console.log('List: fetching finds', type === 'heatmap' ? 'incl. heatmap' : '')
      this.$http.get('/api' + query).then(this.fetchSuccess, function () {
        console.error('List: could not fetch finds')
      })
      if (type === 'heatmap') {
        this.$http.get('/api' + query + '&type=heatmap').then(this.heatmapSuccess, function () {
          console.error('List: could not fetch finds heatmap')
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
        console.log('List: searches saved')
      }).catch(function () {
        console.warn('List: something went wrong')
      })
    }
  },
  ready () {
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
        .filter(f => f.lat)
        .map(f => {
          return {
          identifier: f.identifier,
          title: this.findTitle(f),
          accuracy: f.accuracy || 2000,
          position: {lat: f.lat, lng: f.lng},
          bounds: toPublicBounds(f)
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
