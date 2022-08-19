<template>
  <div>
    <!-- map -->
    <div v-if="finds.length && (markers.length || heatmap.length)">
      <gmap-map :center.sync="map.center" :zoom="map.zoom" class="typology-finds__map-container">
        <gmap-rectangle v-for="f in heatmap" :bounds="f.bounds" :options="f.options"></gmap-rectangle>
        <gmap-marker v-for="f in markers" :position="f.position"></gmap-marker>
      </gmap-map>
    </div>

    <!-- finds -->
    <div v-if="finds.length" class="typology-finds__results-container">
      <h2>Vondsten uit Middeleeuws Metaal</h2>
      <span><a :href="allFindsWithTypologyLink" target="_blank">Bekijk alle vondsten ({{ findsCount }}) behorende tot type {{
          typology.label
        }}&nbsp;({{ typology.code }})</a></span>
      <div class="typology-finds__container">
        <find-event-small v-for="find in finds" :find="find"/>
      </div>
    </div>
    <div v-else class="finds-empty">
      <h1 v-if="typology && typology.code && !fetching">
        Geen resultaten
        <br><small>De databank bevat geen vondsten die aan je selectie '{{ typology.label }} - {{ typology.code }}'
        voldoen.</small>
      </h1>
      <h1 v-else-if="fetching">
        Laden...
      </h1>
    </div>
  </div>
</template>

<script>

const HEATMAP_GRID_BOX_SIZE = 0.0221;  // This represents half of ~5km which is our grid size - https://www.nhc.noaa.gov/gccalc.shtml

import FindEventSmall from '../FindEventSmall.vue'

export default {
  name: 'TypologyFinds',
  props: ['typology'],
  data () {
    return {
      fetching: false,
      finds: [],
      findCoordinates: [],
      findHeatMap: [],
      findsCount: 0,
      mapType: window.typologyMapType || 'markers'
    }
  },
  computed: {
    allFindsWithTypologyLink () {
      if (!this.typology) {
        return
      }

      return window.location.protocol + '//' + window.location.host + '/finds?panid=' + this.typology.code
    },
    map () {
      if (!this.findCoordinates) {
        return {}
      }

      return {
        center: { lat: 50.8, lng: 4.0 },
        zoom: 8,
      }
    },
    markers () {
      if (!this.findCoordinates) {
        return []
      }

      return this
          .findCoordinates
          .map(f => {
            return {
              identifier: f.identifier,
              title: '',
              position: { lat: parseFloat(f.location.lat), lng: parseFloat(f.location.lon) }
            }
          })
    },
    maxAmountOfFindInARectangle () {
      return this.findHeatMap ? Math.max.apply(Math, this.findHeatMap.map(x => x.count)) : 0
    },
    heatmap () {
      var max = this.maxAmountOfFindInARectangle

      return this.findHeatMap && this.findHeatMap.map(x => {
        const gridCentre = x.centre
        return {
          options: {
            fillOpacity: 0.1 + 0.6 * x.count / max,
            strokeWeight: 0
          },
          bounds: {
            north: parseFloat(gridCentre['lat']) + HEATMAP_GRID_BOX_SIZE,
            south: parseFloat(gridCentre['lat']) - HEATMAP_GRID_BOX_SIZE,
            east: parseFloat(gridCentre['lon']) + HEATMAP_GRID_BOX_SIZE,
            west: parseFloat(gridCentre['lon']) - HEATMAP_GRID_BOX_SIZE
          }
        }
      })
    }
  },
  methods: {
    fetchFinds () {
      this.fetching = true
      this.finds = []

      if (!this.typology || !this.typology.code) {
        return
      }

      axios.get('/api/finds?limit=8&order=-identifier&status=Gepubliceerd&panid=' + this.typology.code)
          .then(result => {
            this.finds = result.data.finds
            this.fetching = false
            this.findsCount = result.data.paging.total_count
          })
          .catch(error => {
            console.log(error)
            this.finds = []
            this.fetching = false
          })

      axios.get('/api/finds?type=' + this.mapType + '&status=Gepubliceerd&panid=' + this.typology.code)
          .then(result => {

            if (this.mapType === 'markers') {
              this.findCoordinates = result.data.markers
            } else if (this.mapType === 'heatmap') {
              this.findHeatMap = result.data
            }
          })
          .catch(error => {
            console.log(error)
            this.findCoordinates = []
          })
    }
  },
  mounted () {
    this.fetchFinds()
  },
  components: {
    FindEventSmall
  },
  watch: {
    typology () {
      this.fetchFinds()
    }
  }
}
</script>

<style scoped>
.typology-finds__container {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  margin-top: 0.5rem;
}

.typology-finds__map-container {
  height: 400px;
}

.typology-finds__results-container {
  margin-top: 1rem;
}
</style>
