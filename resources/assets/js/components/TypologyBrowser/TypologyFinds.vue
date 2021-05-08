<template>
  <div class="ui very relaxed items typology-finds__container">
    <find-event-small v-for="find in finds" :find="find"/>
    <div v-if="!finds.length" class="finds-empty">
      <h1 v-if="typology && typology.code && !fetching">
        Geen resultaten
        <br><small>Er zijn geen vondsten die onder typologie {{typology.code}} vallen.</small>
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
        finds: []
      }
    },
    methods: {
      fetchFinds() {
        this.fetching = true
        this.finds = []

        if (!this.typology || !this.typology.code) {
          return
        }

        axios.get('/api/finds?order=-identifier&status=Gepubliceerd&panid=' + this.typology.code)
          .then(result => {
            this.finds = result.data
            this.fetching = false
          })
          .catch(error => {
            console.log(error)
            this.finds = []
            this.fetching = false
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
  }
</style>
