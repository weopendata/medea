<template>
  <div class="item fe">
    <a class="big image fe-image" href="/finds/{{find.identifier}}">
      <img class="fe-img" :src="image.resized" v-for="image in find.object.photograph">
      <div class="fe-img fe-img-placeholder" v-if="!find.object.photograph">Afbeelding niet beschikbaar</div>
    </a>
    <div class="content">
      <a class="header" href="/finds/{{find.identifier}}">#{{find.identifier}} {{find.object.category}} {{find.object.objectMaterial}}  {{find.object.productionEvent.productionTechnique.type}}</a>
      <div class="meta">
        <span>Gevonden {{find.findDate?'op '+find.findDate:''}} in de buurt van <u @click="mapFocus('city')">{{find.findSpot.location.address&&find.findSpot.location.address.locality}}</u></span>
      </div>
      <div class="description">
        <object-features :find="find"></object-features>
      </div>
      <div class="extra">
        <a class="ui green button" href="/finds/{{find.identifier}}" v-if="user.expert&&!classificationCount&&find.object.objectValidationStatus == 'gevalideerd'">
          <i class="tag icon"></i>
          Classificeren
        </a>
        <a class="ui blue button" href="/finds/{{find.identifier}}" v-if="classificationCount">
          <i class="tag icon"></i>
          {{classificationCount}} classificaties bekijken
        </a>
        <a class="ui green button" href="/finds/{{find.identifier}}/edit" v-if="find.object.objectValidationStatus == 'revisie nodig'">
          <i class="pencil icon"></i>
          Bewerken
        </a>
        <a class="ui green button" href="/finds/{{find.identifier}}" v-if="user.validator&&find.object.objectValidationStatus == 'in bewerking'">
          Valideren
        </a>
        <button class="ui blue button" @click="mapFocus" v-if="hasLocation">
          <i class="marker icon"></i>
          Op de kaart
        </button>
        <a class="ui basic small icon black button" href="/finds/{{find.identifier}}/edit" v-if="(user.email==find.person.email)||user.validator">
          <i class="pencil icon"></i>
        </a>
        <button class="ui basic small icon button" @click="rm" v-if="user.admin&&find.identifier">
          <i class="trash icon"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import ObjectFeatures from './ObjectFeatures';

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
    }
  },
  methods: {
    rm () {
      this.find.object.objectValidationStatus = 'verwijderd'
      var $root = this.$root
      this.$http.delete('/finds/' + this.find.identifier).then(function (res) {
        console.log('removed', this.find.identifier)
        $root.fetch()
      });
    },
    mapFocus (accuracy) {
      if (!this.find.findSpot.location.lat) {
        return alert('LatLng is missing, this will never happen')
      }
      accuracy = accuracy == 'city' ? 7000 : 0
      this.$dispatch('mapFocus', {lat:this.find.findSpot.location.lat, lng:this.find.findSpot.location.lng}, accuracy || this.find.findSpot.location.accuracy || 100)
    }
  }
}
</script>