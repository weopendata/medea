<template>
  <div class="item fe">
    <a class="big image fe-image" href="/finds/{{find.id}}">
      <img :src="'/uploads/' + src" v-for="src in find.object.images | limitBy 2">
    </a>
    <div class="content">
      <a class="header" href="/finds/{{find.id}}">{{find.object.description}}</a>
      <div class="meta">
        <span>Gevonden op {{find.findDate}} in de buurt van <u>{{find.findSpot.location.address.locality}}</u></span>
      </div>
      <div class="description">
        <object-features :find="find"></object-features>
      </div>
      <div class="extra">
        <a class="ui blue button" href="/finds/{{find.id}}" v-if="find.object.validationCount<8&&find.object.classificationCount<2">
          <i class="check icon"></i>
          Valideren
        </a>
        <button class="ui green button" v-if="user.isFindExpert&&!find.object.classificationCount">
          <i class="tag icon"></i>
          Classificeer
        </button>
        <a class="ui blue button" href="/finds/{{find.id}}" v-if="find.object.classificationCount">
          <i class="tag icon"></i>
           {{find.object.classificationCount}} classificaties bekijken
        </a>
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
  ready () {
    this.$http.get('/api-mock/finds.json').then(function (res) {
      this.findEvents = res.data
    }, function () {
      console.error('could not find findevents')
    });
  }
}
</script>