<template>
  <div class="item fe">
    <a class="big image fe-image" href="/finds/{{find.identifier}}">
      <img class="fe-img" :src="src.identifier || src" v-for="src in find.object.images">
      <div class="fe-img fe-img-placeholder" v-if="!find.object.images">Afbeelding niet beschikbaar</div>
    </a>
    <div class="content">
      <a class="header" href="/finds/{{find.identifier}}">{{find.object.description}}</a>
      <div class="meta">
        <span>Gevonden op {{find.identifier}} in de buurt van <u>{{find.findSpot.location.address&&find.findSpot.location.address.locality}}</u></span>
      </div>
      <div class="description">
        <object-features :find="find"></object-features>
      </div>
      <div class="extra">
        <a class="ui blue button" href="/finds/{{find.identifier}}" v-if="find.object.validationCount<8&&find.object.classificationCount<2">
          <i class="check icon"></i>
          Valideer
        </a>
        <a class="ui green button" href="/finds/{{find.identifier}}" v-if="user.isFindExpert&&!find.object.classificationCount">
          <i class="tag icon"></i>
          Classificeer
        </a>
        <a class="ui blue button" href="/finds/{{find.identifier}}" v-if="find.object.classificationCount">
          <i class="tag icon"></i>
          {{find.object.classificationCount}} classificaties bekijken
        </a>
        <button class="ui red button" @click="rm" v-if="user.isAdmin&&find.identifier">
          <i class="trash icon"></i>
          Verwijderen
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
  methods: {
    rm () {
      this.$http.delete('/finds/' + this.find.identifier).then(function (res) {
        console.log('removed', this.find.identifier)
        this.$root.fetch()
      });
    }
  }
}
</script>