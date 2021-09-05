<template>
  <div>
    <!-- map -->
    <div v-if="finds.length && coordinates.length">
      <gmap-map :center.sync="map.center" :zoom="map.zoom" class="typology-finds__map-container">
        <gmap-marker v-for="f in coordinates" :position="f.position"></gmap-marker>
      </gmap-map>
    </div>

    <!-- finds -->
    <div v-if="finds.length" class="typology-finds__results-container">
      <h2>Vondsten uit Middeleeuws Metaal</h2>
      <span><a :href="allFindsWithTypologyLink" target="_blank">Bekijk alle vondsten ({{ findsCount }}) behorend tot type {{ typology.label }}&nbsp;({{typology.code }})</a></span>
      <div class="typology-finds__container">
        <find-event-small v-for="find in finds" :find="find"/>
      </div>
    </div>
    <div v-else class="finds-empty">
      <h1 v-if="typology && typology.code && !fetching">
        Geen resultaten
        <br><small>De databank bevat geen vondsten die aan je selectie '{{typology.label}} - {{typology.code}}' voldoen.</small>
      </h1>
      <h1 v-else-if="fetching">
        Laden...
      </h1>
    </div>
  </div>
</template>

<script>
  export default {
    name: "TypologyFinds",
    props: ['typology'],
    data() {
      return {
        fetching: false,
        finds: [],
        findCoordinates: [],
        findsCount: 0,
      }
    },
    computed: {
      allFindsWithTypologyLink () {
        if (!this.typology) {
          return
        }

        return window.location.protocol + "//" + window.location.host + '/finds?panid=' + this.typology.code
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
      coordinates() {
        if (!this.findCoordinates) {
          return []
        }

        return this
          .findCoordinates
          .map(f => {
            return {
              identifier: f.identifier,
              title: '',
              position: {lat: parseFloat(f.location.lat), lng: parseFloat(f.location.lng)}
            }
          })
      }
    },
    methods: {
      fetchFinds() {
        this.fetching = true
        this.finds = []

        if (!this.typology || !this.typology.code) {
          return
        }

        axios.get('/api/finds?limit=6&order=-identifier&status=Gepubliceerd&panid=' + this.typology.code)
          .then(result => {
            this.finds = result.data
            this.fetching = false
            this.findsCount = result.headers['x-total']
          })
          .catch(error => {
            console.log(error)
            this.finds = []
            this.fetching = false
          })

        axios.get('/api/finds?type=markers&status=Gepubliceerd&panid=' + this.typology.code)
          .then(result => {
            this.findCoordinates = result.data
          })
          .catch(error => {
            console.log(error)
            this.findCoordinates = []
          })
      }
    },
    mounted() {
      this.fetchFinds()
    },
    watch: {
      typology() {
        this.fetchFinds()
      }
    }
  }
</script>

<style scoped>
  .typology-finds__container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-evenly;
    margin-top: 0.5rem;
  }

  .typology-finds__map-container {
    height: 400px;
  }

  .typology-finds__results-container {
    margin-top: 1rem;
  }
</style>
