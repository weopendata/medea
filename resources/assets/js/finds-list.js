import FindsList from './components/FindsList'
import FindsFilter from './components/FindsFilter'
import MapControls from './components/MapControls'
import {load, Map as GoogleMap, Marker, Rectangle, InfoWindow} from 'vue-google-maps'
// import {load, Map, Marker, Circle} from 'vue-google-maps/src/main.js'
import DevBar from './components/DevBar'

import Notifications from './mixins/Notifications'
import { inert, toPublicBounds } from './const.js'

import parseLinkHeader from 'parse-link-header'

const HEATMAP_RADIUS = 0.05

// Parse link header
function getPaging (header) {
  if (typeof header === 'function') {
    return parseLinkHeader(header('link'))
  }
  if (typeof header === 'string') {
    return parseLinkHeader(header)
  }
  return header && header.map && header.map.Link && parseLinkHeader(header.map.Link[0])
}

let listQuery, heatmapQuery

new window.Vue({
  el: 'body',
  data () {
    return {
      paging: window.link ? parseLinkHeader(window.link) : {},
      finds: window.initialFinds || [],
      fetching: false,
      filterState: window.filterState || console.error('filterState warning') || {},
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

      // Do not push state on first load
      if (listQuery) {
        window.history.pushState({}, document.title, query)
      }

      // Do not fetch same query twice
      if (listQuery !== query) {
        listQuery = query
        this.fetching = true
        this.$http.get('/api' + query)
          .then(function (res) {
            this.paging = getPaging(res.headers)
            this.finds = res.data
            this.fetching = false
          })
          .catch(function () {
            this.paging = {}
            this.finds = []
            console.error('List: could not fetch finds')
          })
      }

      // Do not fetch same query twice
      if (type === 'heatmap' && heatmapQuery !== query) {
        heatmapQuery = query
        this.$http.get('/api' + query + '&type=heatmap')
          .then(({ data }) => this.rawmap = data)
          .catch(function () {
            this.rawmap = []
            console.error('List: could not fetch finds heatmap')
          })
      }
    },
    mapToggle (v) {
      if (this.filterState.type === v) {
        this.$set('filterState.type', false)
      } else {
        this.$set('filterState.type', v)
        if (v === 'heatmap') {
          this.fetch('heatmap')
        }
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
        .filter(f => f.lat && f.accuracy == 1)
        .map(f => {
          return {
            title: this.findTitle(f),
            position: { lat: parseFloat(f.lat), lng: parseFloat(f.lng) }
          }
        })
    },
    rectangable (finds) {
      return finds
        .filter(f => f.lat)
        .map(f => {
          return {
            title: this.findTitle(f),
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
