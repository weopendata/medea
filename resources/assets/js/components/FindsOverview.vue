<template>
  <div>
    <div class="ui container" :class="{fetching:fetching}">
      <div class="list-left">
        <finds-filter
            :name.sync="filterName"
            :model.sync="filterState"
            :saved="saved"
            :facets="facets"
            :fetching="fetchingFacets"
            :excludedFacets="excludedFacets"
            @filtersChanged="filtersChanged"
        />
      </div>
      <div class="list-right">
        <div class="list-controls">
          <span class="finds-order" v-if="displayOrderByOptions">
            Sorteren op:
            <a @click.prevent="sortBy('findDate')"
               :class="{active: filterState.order == 'findDate', reverse:filterState.order == '-findDate'}">vondstdatum</a>
            <a @click.prevent="sortBy('identifier')"
               :class="{active: filterState.order == 'identifier', reverse:filterState.order == '-identifier'}">vondstnummer (ID)</a>
          </span>

          <span class="finds-card-style" v-if="displayCardStyleOptions">
            Kaartstijl:
            <a @click.prevent="setCardStyle('list')"
               :class="{active: viewState.cardStyle == 'list'}">Lijst</a>
            <a @click.prevent="setCardStyle('tile')"
               :class="{active: viewState.cardStyle == 'tile'}">Tegels</a>
          </span>

          <label class="pull-right">
            <button class="ui basic button" :class="{green:filterState.type=='map'}" @click="mapToggle('map')">Kaart
            </button>
          </label>
        </div>

        <div v-if="filterState.type" id="mapview" class="card mapview">
          <div v-if="!HelpText.map" class="card-help">
            <h1>Kaart</h1>
            <p>
              Deze kaart geeft de gebieden aan waar de vondsten die voldoen aan de zoekcriteria gevonden werden.
            </p>
            <p>
              <img src="/assets/img/help-area.png" height="40px"> Ruwe vondstlocatie
            </p>
            <p>
              <img src="/assets/img/help-marker.png" height="40px"> Precieze vondstlocatie is enkel zichtbaar voor eigen
              vondsten.
            </p>
            <p>
              <button class="ui green button" @click="hideHelp('map')">OK</button>
            </p>
          </div>

          <gmap-map :center.sync="map.center" :zoom.sync="map.zoom" class="fe-overview-map">
            <gmap-rectangle v-for="f in heatmap" :bounds="f.bounds" :options="f.options"></gmap-rectangle>
            <gmap-marker v-for="f in markable" @g-click="mapClick(f)" @g-mouseover="mapClick(f)"
                         :position.sync="f.position"></gmap-marker>
            <gmap-rectangle v-for="f in rectangable" @g-click="mapClick(f)" :bounds="f.bounds"
                            :options="markerOptions"></gmap-rectangle>
            <div slot="visible"> <!-- deprecated from Vue 2.6 onwards -->
              <div class="gm-panel"
                   style="direction: ltr; overflow: hidden; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;right: 10px;"
                   v-if="map.info" v-html="map.info"></div>
              <div class="gm-panel"
                   style="direction: ltr; overflow: hidden; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;top:auto;left: 10px;bottom: 50px;"
                   @click="showHelp(filterState.type=='heatmap'?'heatmap':'map')">Help
              </div>
              <div class="gm-panel"
                   style="direction: ltr; overflow: hidden; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;top:auto;left: 10px;bottom: 10px;">
                Alle regio's van de vondsten die aan de zoekcriteria voldoen worden op de kaart getoond.
              </div>
            </div>
          </gmap-map>
        </div>
        <finds-list
            :finds="finds"
            :fetching="fetching"
            :user="user"
            :paging="paging"
            :saved="saved"
            :filterName="filterName"
            :filterState="filterState"
            :cardStyle="cardStyle"
            @saveSearch="saveSearch"
            @rmSearch="rmSearch"
            @updateFilterName="updateFilterName"
            @filtersChanged="filtersChanged"
        >
        </finds-list>
      </div>
    </div>
  </div>
</template>


<script>
import FindsList from './FindsList.vue'
import FindsFilter from './FindsFilter.vue'
import MapControls from './MapControls.vue'
import DevBar from './DevBar.vue'

import Notifications from '../mixins/Notifications'
import HelpText from '../mixins/HelpText'
import { inert, toPublicBounds, findTitle } from '../const.js'
import ls from 'local-storage'

import { fetchFinds, fetchFindsMap } from '../api/finds.js'

const HEATMAP_RADIUS = 0.0221;  // This represents half of ~5km which is our grid size - https://www.nhc.noaa.gov/gccalc.shtml

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

let listQuery, heatmapQuery

export default {
  data () {
    return {
      paging: {},
      finds: [],
      facets: {},
      excludedFacets: window.excludedFacets || [],
      fetching: false,
      fetchingFacets: false,
      filterState: window.filterState || ls('filterState') || {},
      viewState: window.viewState || {},
      filterName: '',
      user: window.medeaUser,
      map: {
        type: false,
        info: null,
        center: { lat: 50.9, lng: 4.3 },
        zoom: 8
      },
      markerOptions: {
        fillOpacity: 0,
        strokeWeight: 1
      },
      rawmap: null,
      loaded: false,
      cancelTokens: {}
    }
  },
  computed: {
    cardStyle () {
      if (!this.viewState.cardStyle || !['list', 'tile'].includes(this.viewState.cardStyle)) {
        return 'list'
      }

      return this.viewState.cardStyle
    },
    displayOrderByOptions () {
      return !!this.viewState.displayOrderByOptions
    },
    displayCardStyleOptions () {
      return !!this.viewState.displayCardStyleOptions
    },
    markable () {
      return this.finds
          .filter(f => f.lat && f.accuracy == 1)
          .map(f => {
            return {
              identifier: f.identifier,
              title: findTitle(f),
              position: { lat: parseFloat(f.lat), lng: parseFloat(f.lng) }
            }
          })
    },
    rectangable () {
      return this.finds
          .filter(f => f.lat)
          .map(f => {
            return {
              title: findTitle(f),
              bounds: toPublicBounds(f)
            }
          })
    },
    heatmapMax () {
      return this.rawmap ? Math.max.apply(Math, this.rawmap.map(x => x.count)) : 0
    },
    heatmap () {
      var max = this.heatmapMax

      return this.rawmap && this.rawmap.map(x => {
        const gridCentre = x.centre
        return {
          options: {
            fillOpacity: 0.1 + 0.6 * x.count / max,
            strokeWeight: 0
          },
          bounds: {
            north: parseFloat(gridCentre['lat']) + HEATMAP_RADIUS,
            south: parseFloat(gridCentre['lat']) - HEATMAP_RADIUS,
            east: parseFloat(gridCentre['lon']) + HEATMAP_RADIUS,
            west: parseFloat(gridCentre['lon']) - HEATMAP_RADIUS
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
    updateFilterName (name) {
      this.filterName = name;
    },
    filtersChanged (v) {
      if (v) {
        this.filterState = Object.assign(this.filterState, v);
      }

      this.fetch();
    },
    relevant (find) {
      if (find.validation == 'Klaar voor validatie') {
        if (!this.user.validator) {
          console.warn('List: Security error, this user is not allowed to see this find')
        }
      } else if (find.validation != 'Gepubliceerd' && !this.user.administrator) {
        console.warn('List: Security error, this user is not allowed to see this find')
      }

      return true
    },
    resetFilters () {
      this.filterState = {
        category: null,
        status: null,
        embargo: null,
        period: null,
        technique: null,
        modification: null,
        objectMaterial: null,
        collections: null,
        volledigheid: null,
        merkteken: null,
        opschrift: null,
        photographCaption: null,
        startYear: null,
        endYear: null,
        panid: null,
        findSpotLocation: null,
        excavationLocation: null
      };

      this.fetch()
    },
    fetch () {
      var model = inert(this.filterState)
      var type = model.type

      if (model.name) {
        delete model.name
      }

      if (model.myfinds && this.user.isGuest) {
        delete model.myfinds
      }

      if (model.type) {
        delete model.type
      }

      var query = Object
          .keys(model)
          .map(function (key, index) {
            return model[key] && model[key] !== '*' ? key + '=' + encodeURIComponent(model[key]) : null
          })
          .filter(Boolean)
          .join('&')

      query = query ? '/finds?' + query : '/finds?'

      // Do not fetch same query twice
      if (listQuery !== query) {
        this.fetching = true

        fetchFinds(query)
            .then(response => {
              this.finds = response.data.finds
              this.paging = response.data.paging
              this.facets = response.data.facets
              this.fetching = false
            })
            .catch(error => {
              if (axios.isCancel(error)) {
                return
              }

              console.error('Could not fetch list of finds, something went wrong.', error)

              this.paging = {}
              this.finds = []
              this.facets = {}
              this.fetching = false
            })

        // Do not push state on first load
        if (listQuery) {
          window.history.pushState({}, document.title, query)
        }
        listQuery = query
      }

      // Do not fetch same query twice
      if (heatmapQuery !== query) {
        heatmapQuery = query

        fetchFindsMap(query)
            .then(({ data }) => {
              this.rawmap = data
            })
            .catch(function (error) {
              if (axios.isCancel(error)) {
                return
              }

              console.error('Could not fetch finds heatmap', error)

              this.rawmap = []
            })
      }

      // Store the state in local storage
      ls('filterState', model)
    },
    mapToggle (v) {
      if (this.filterState.type === v) {
        this.$set(this.filterState, 'type', false)
      } else {
        this.$set(this.filterState, 'type', v)
        this.fetch('heatmap')
      }
    },
    mapClick (f) {
      this.map.info = f.title
      if (f.identifier && f.position) {
        this.map.info += '<br><a href="/finds/' + f.identifier + '">Vondstfiche bekijken &rarr;</a>'
      }
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
    setCardStyle (cardStyle) {
      this.viewState.cardStyle = cardStyle
    },
    persistSearches () {
      // Save the new list of favorites
      axios.put('/persons/' + this.user.id, {
        id: this.user.id,
        savedSearches: this.user.savedSearches
      })
          .then(res => {
            //
          }).catch(err => {

      });
    },
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
      saved.push({ name: name, state: this.filterState })
      this.user.savedSearches = JSON.stringify(saved)
      this.persistSearches()
    },
    rmSearch () {
      // Remove from saved searches
      this.user.savedSearches = JSON.stringify(this.saved.filter(s => s.name !== this.filterName && s.name && s.name.length > 0))
      //this.persistSearches()
    }
  },
  mounted () {
    // If the order by is empty when the app is ready,
    // make sure the default sorting is set to the id, in a descending way
    if (!this.filterState.order) {
      this.filterState.order = '-identifier'
    }

    this.fetch()

    if (this.filterState.type && !this.loaded) {
      this.loaded = true
    }
  },
  watch: {
    'filterState.type' (type) {
      if (type && !this.loaded) {
        this.loaded = true
      }
      if (type === 'heatmap') {
        this.fetch()
      }
    },
    'user': {
      deep: true,
      handler (user) {
        localStorage.debugUser = JSON.stringify(user)
      }
    }
  },
  mixins: [Notifications, HelpText],
  components: {
    DevBar,
    FindsFilter,
    FindsList,
    MapControls
  }
}
</script>
