<template>
  <div class="item fe">
    <a class="big image fe-image" href="/finds/{{find.id}}">
      <img :src="'/uploads/' + src" v-for="src in find.object.images" v-if="find.object.images">
    </a>
    <div class="content">
      <a class="header" href="/finds/{{find.id}}">{{find.object.description}}</a>
      <div class="meta">
        <span>Gevonden op {{find.identifier}} in de buurt van <u>{{find.findSpot.location.address&&find.findSpot.location.address.locality}}</u></span>
      </div>
      <div class="description">
        <object-features :find="find"></object-features>
      </div>
      <div class="extra">
        <a class="ui blue button" href="/finds/{{find.id}}" v-if="find.object.validationCount<8&&find.object.classificationCount<2">
          <i class="check icon"></i>
          Valideer
        </a>
        <a class="ui green button" href="/finds/{{find.id}}" v-if="user.isFindExpert&&!find.object.classificationCount">
          <i class="tag icon"></i>
          Classificeer
        </a>
        <a class="ui blue button" href="/finds/{{find.id}}" v-if="find.object.classificationCount">
          <i class="tag icon"></i>
          {{find.object.classificationCount}} classificaties bekijken
        </a>
        <button class="ui red button" @click="rm" v-if="user.isAdmin&&find.object.identifier">
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
  data () {
    return {
      findEvents: []
    }
  },
  methods: {
    rm () {
      this.$http.delete('/finds/'+(this.find.object.identifier-1), 'yes', {emulateHTTP:true}).then(function (res) {
        console.log('removed', this.find.object.identifier)
      });
    }
  }
}
</script>