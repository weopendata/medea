<template>
  <div>
    <input type="text" @input="updateSearchQuery($event.target.value)" :value="searchQuery" />

    <typology-tree
            v-for="(typology, index) in typologyTree"
            :root="typology"
            :key="'typology__' + typology.code"
            :searchQuery="searchQuery"
      />
  </div>
</template>

<script>
  import TypologyTree from "./TypologyBrowser/TypologyTree";
  
  export default {
    name: "TypologyBrowser",
    components: {TypologyTree},
    data () {
      return {
        typologyTree: [],
        searchQuery: ''
      }
    },
    methods: {
      updateSearchQuery: _.debounce(function (val) {
        this.searchQuery = val
      }, 500)
    },
    mounted () {
      this.typologyTree = window.typologyTree
    }
  }
</script>

<style scoped>

</style>
