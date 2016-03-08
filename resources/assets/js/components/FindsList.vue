<template>
  <div v-for="findEvent in findEvents">
    <h1 v-text="findEvent.object.productionEvent.classification.description"></h1>
    <pre v-text="findEvent|json"></pre>
  </div>
</template>

<script>
export default {
  props: ['columns', 'finds'],
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