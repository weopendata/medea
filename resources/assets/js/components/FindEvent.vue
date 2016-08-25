<template>
  <div class="card">
    <div class="card-img">
      <a :href="uri" class="card-img-abs" style="background-image:url({{cardCover}})"></a>
    </div>
    <div class="card-content">
      <div class="card-textual">
        <a :href="uri" class="card-title">{{findTitle}}</a>
        <span>Gevonden <span v-if="find.findDate">op {{find.findDate|fromDate}}</span> in de buurt van <a href="#mapview" @click="mapFocus('city')">{{find.findSpot.location.address&&find.findSpot.location.address.locality}}</a></span>
        <br>Status: {{ find.object.objectValidationStatus }}
      </div>
      <div class="card-bar">
        <a class="btn" :href="uri" v-if="user.vondstexpert&&!classificationCount&&find.object.objectValidationStatus == 'gevalideerd'">
          <i class="tag icon"></i>
          Classificeren
        </a>
        <a class="btn" :href="uri" v-if="classificationCount&&find.object.objectValidationStatus == 'gevalideerd'">
          <i class="tag icon"></i>
          {{classificationCount}} classificatie{{classificationCount > 1 ? 's' : ''}} bekijken
        </a>
        <a class="btn" :href="uri" v-if="user.validator&&find.object.objectValidationStatus == 'in bewerking'">
          Valideren
        </a>
        <a class="btn" :href="uri" v-if="!user.validator&&!user.vondstexpert&&find.object.objectValidationStatus == 'gevalideerd'">
          Bekijken
        </a>
        <a class="btn" href="#mapview" @click="mapFocus" v-if="hasLocation">
          <i class="marker icon"></i>
          Op de kaart
        </a>
        <a class="btn" :href="uriEdit" v-if="find.object.objectValidationStatus == 'revisie nodig'">
          <i class="pencil icon"></i>
          Bewerken
        </a>
        <button class="btn btn-icon pull-right" @click="rm" v-if="user.administrator&&find.identifier">
          <i class="trash icon"></i>
        </button>
        <a class="btn btn-icon pull-right" :href="uriEdit" v-if="(user.email==find.person.email)||user.validator">
          <i class="pencil icon"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import ObjectFeatures from './ObjectFeatures';
import {fromDate} from '../const.js'

export default {
  props: ['user', 'find'],
  components: {
    ObjectFeatures
  },
  computed: {
    classificationCount () {
      return this.find.object.productionEvent && this.find.object.productionEvent.productionClassification && this.find.object.productionEvent.productionClassification.length
    },
    hasLocation () {
      return this.find.findSpot.location && this.find.findSpot.location.lat
    },
    cardCover () {
      return this.find.object.photograph && this.find.object.photograph[0] && encodeURI(this.find.object.photograph[0].resized)
    },
    uri () {
      return '/finds/' + this.find.identifier
    },
    uriEdit () {
      return this.uri + '/edit'
    },
    findTitle () {
      // Not showing undefined and onbekend in title
      var title = [
        this.find.object.objectCategory,
        this.find.object.period,
        this.find.object.objectMaterial
      ].filter(f => f && f !== 'onbekend').join(', ')

      title += ' (ID-' + this.find.identifier + ')'

      return title;
    }
  },
  methods: {
    rm () {
      if (!confirm('Ben je zeker dat vondst #' + this.find.identifier + ' verwijderd mag worden?')) {
        return
      }
      this.$http.delete('/finds/' + this.find.identifier).then(function (res) {
        console.log('removed', this.find.identifier)
        this.$root.fetch()
        this.find.object.objectValidationStatus = 'verwijderd'
      });
    },
    mapFocus (accuracy) {
      if (!this.find.findSpot.location.lat) {
        return alert('LatLng is missing, this will never happen')
      }
      accuracy = accuracy == 'city' ? 7000 : 0
      this.$dispatch('mapFocus', {lat:this.find.findSpot.location.lat, lng:this.find.findSpot.location.lng}, accuracy || this.find.findSpot.location.accuracy || 1)
    }
  },
  filters: {
    fromDate
  }
}
</script>