<template>
  <div class="card">
    <div class="card-img">
      <a :href="uri" class="card-img-abs" v-if="cardCover" :style="{ 'background-image': 'url(\'' + find.photograph + '\')' }"></a>
      <a :href="uri" class="card-img-abs" v-else style="background:#fff"></a>
    </div>
    <div class="card-content">
      <div class="card-textual">
        <a :href="uri" class="card-title">{{findTitle}}</a>
        <span>Gevonden <span v-if="find.findDate">op {{find.findDate|fromDate}}</span> in de buurt van <a href="#mapview" @click="mapFocus('city')">{{find.locality}}</a></span>
        <div>
          Status: {{ find.validation }}
          <span v-if="classificationCount&&find.validation == 'Gepubliceerd'">
            - {{classificationCount}} classificatie{{classificationCount > 1 ? 's' : ''}}
          </span>
        </div>
        <div v-if="find.collectionTitle">
          Collectie: {{ find.collectionTitle }}
        </div>
      </div>
      <div class="card-bar">
        <a class="btn" :href="uri" v-if="user.validator&&find.validation == 'Klaar voor validatie'">
          Valideren
        </a>
        <a class="btn" :href="uri" v-if="find.validation == 'Gepubliceerd'">
          Bekijken
        </a>
        <a class="btn" href="#mapview" @click="mapFocus" v-if="hasLocation">
          <i class="marker icon"></i>
          Op de kaart
        </a>
        <a class="btn" :href="uriEdit" v-if="editable && (user.email==find.email)">
          <i class="pencil icon"></i>
          Bewerken
        </a>
        <button class="btn btn-icon pull-right" @click="rm" v-if="user.administrator||editable">
          <i class="trash icon"></i>
        </button>
        <a class="btn btn-icon pull-right" :href="uriEdit" v-if="user.administrator||editable">
          <i class="pencil icon"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import ObjectFeatures from './ObjectFeatures';
import { fromDate, findTitle } from '../const.js'

export default {
  props: ['user', 'find'],
  components: {
    ObjectFeatures
  },
  computed: {
    editable () {
      return ['Aan te passen', 'Voorlopige versie'].indexOf(this.find.validation) !== -1
      // Finder    if 'Aan te passen' or 'Voorlopige versie'
      // Validator if 'Klaar voor validatie'
      // Admin     always
      var s = this.find.validation
      return this.user.email && (
        (this.user.email === this.find.email && ['Aan te passen', 'Voorlopige versie'].indexOf(s) !== -1) ||
        (this.user.validator && s === 'Klaar voor validatie')
      )
    },
    classificationCount () {
      return this.find.classificationCount
    },
    hasLocation () {
      return this.find.lat
    },
    cardCover () {
      return this.find.photograph;
    },
    uri () {
      return '/finds/' + this.find.identifier
    },
    uriEdit () {
      return this.uri + '/edit'
    },
    findTitle () {
      return findTitle(this.find)
    }
  },
  methods: {
    rm () {
      if (!confirm('Ben je zeker dat vondst #' + this.find.identifier + ' verwijderd mag worden?')) {
        return
      }
      this.$http.delete('/finds/' + this.find.identifier).then(function (res) {
        this.$root.fetch()
        this.find.validation = 'Wordt verwijderd'
      });
    },
    mapFocus (accuracy) {
      if (!this.find.lat) {
        return alert('LatLng is missing, this will never happen')
      }
      accuracy = parseInt(accuracy == 'city' ? 7000 : this.find.accuracy || 1) * 2

      this.$dispatch('mapFocus', {lat:parseFloat(this.find.lat), lng:parseFloat(this.find.lng)}, accuracy)
    }
  },
  filters: {
    fromDate
  }
}
</script>