<template>
  <div class="typology-browser__container">
    <div class="typology-tree-view">
      <input type="text" @input="updateSearchQuery($event.target.value)" :value="searchQuery"/>

      <typology-tree
              v-for="(typology, index) in typologyTree"
              :root="typology"
              :key="'typology__' + typology.code"
              :searchQuery="searchQuery"
      />
    </div>

    <div class="typology-details__container" v-if="selectedTypology && selectedTypology.code">
      <tabs :tabs="tabs">
        <div slot="Typologie Info" class="typology-details__info">
          <typology-info-card :typology="selectedTypology"/>
        </div>

        <div slot="Vondsten" class="typology-details__find-results">
          <typology-finds :typology="selectedTypology" />
        </div>
      </tabs>
    </div>
  </div>
</template>

<script>
  import TypologyTree from "./TypologyBrowser/TypologyTree";
  import TypologyFinds from "./TypologyBrowser/TypologyFinds";
  import TypologyInfoCard from "./TypologyBrowser/TypologyInfoCard";

  import $bus from '../helpers/bus'
  import Tabs from './Tabs.vue'

  export default {
    name: "TypologyBrowser",
    data() {
      return {
        typologyTree: [],
        searchQuery: '',
        selectedTypology: {},
        tabs: [
          'Typologie Info',
          'Vondsten'
        ]
      }
    },
    methods: {
      updateSearchQuery: _.debounce(function (val) {
          this.searchQuery = val
        }
        , 500
      ),
      updateSelectedTypology (selection) {
        this.selectedTypology = selection.typology
      }
    },
    mounted() {
      this.typologyTree = window.typologyTree

      $bus.$on('typologySelected', this.updateSelectedTypology)
    },
    beforeDestroy() {
      $bus.$off('typologySelected')
    },
    components: {
      TypologyTree,
      Tabs,
      TypologyInfoCard,
      TypologyFinds
    }
  }
</script>

<style scoped>
  .typology-browser__container {
    display:flex;
    justify-content: space-between;
    max-width: 80%;
    margin-left: auto;
    margin-right: auto;
  }

  .typology-tree-view {
    margin-left: 1rem;
    max-width: 33%;
    min-width: 33%
  }

  .typology-details__container {
    min-width: 67%;
    max-width: 67%;
  }

  .typology-details__info {

  }

  .typology-details__find-results {

  }
</style>
