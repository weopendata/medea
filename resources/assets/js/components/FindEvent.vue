<template>
  <div class="item fe">
    <a class="big image fe-image" href="/finds/{{find.identifier}}">
      <img class="fe-img" :src="src.identifier" v-for="src in find.object.photograph">
      <div class="fe-img fe-img-placeholder" v-if="!find.object.photograph">Afbeelding niet beschikbaar</div>
    </a>
    <div class="content">
      <a class="header" href="/finds/{{find.identifier}}">#{{find.identifier}} {{find.object.category}} {{find.object.objectMaterial}} {{find.object.category}}</a>
      <div class="meta">
        <span>Gevonden {{find.findDate?'op '+find.findDate:''}} in de buurt van <u>{{find.findSpot.location.address&&find.findSpot.location.address.locality}}</u></span>
      </div>
      <div class="description">
        <object-features :find="find"></object-features>
      </div>
      <div class="extra">
        <a class="ui green button" href="/finds/{{find.identifier}}" v-if="user.expert&&!find.object.classificationCount&&find.object.objectValidationStatus !== 'in bewerking'">
          <i class="tag icon"></i>
          Classificeren
        </a>
        <a class="ui blue button" href="/finds/{{find.identifier}}" v-if="find.object.classificationCount">
          <i class="tag icon"></i>
          {{find.object.classificationCount}} classificaties bekijken
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
    mapFocus () {
      this.$dispatch('mapFocus', {lat:this.find.findSpot.location.lat, lng:this.find.findSpot.location.lng}, 100)
    }
  }
}
</script>