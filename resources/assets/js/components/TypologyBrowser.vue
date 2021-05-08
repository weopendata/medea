<template>
  <div class="typology-browser__container">
    <div class="typology-tree-view">
      <div class="typology-browser__query-input ui icon input">
        <i class="search icon"/>
        <input placeholder="Zoeken..." type="text" @input="updateSearchQuery($event.target.value)" :value="searchQuery"/>
      </div>

      <div class="typology-tree__container ui card" :style="treeStyle">
        <typology-tree
                v-for="(typology, index) in typologyTree"
                :root="typology"
                :key="'typology__' + typology.code + '__index' + index"
                :searchQuery="searchQuery"
                @childMatchesQuery="updateVisibility"
        />
      </div>

      <div v-if="!hasVisibleTypologies && hasValidSearchQuery">
        Geen typologie gevonden die voldoet aan je zoekopdracht
      </div>
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
        typologyMap: {},
        searchQuery: '',
        selectedTypology: {},
        visibleTypologies: {},
        tabs: [
          'Typologie Info',
          'Vondsten'
        ]
      }
    },
    computed: {
      hasValidSearchQuery () {
        return this.searchQuery && this.searchQuery.length >= 2
      },
      treeStyle () {
        if (!this.hasValidSearchQuery) {
          return
        }

        if (this.hasVisibleTypologies ) {
          return
        }

        return {visibility: 'hidden', height: '0px', border: '0px', padding: '0px'}
      },
      hasVisibleTypologies () {
        var values = Object.values(this.visibleTypologies)

        if (!values || values.length == 0) {
          return true
        }

        return values.filter(r => r).length > 0
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

        if (this.selectedTypology.code) {
          window.location.hash = '#' + this.selectedTypology.code
        }
      },
      updateVisibility (mainLevelTypology) {
        this.visibleTypologies['code_' + mainLevelTypology.code] = mainLevelTypology.match
      }
    },
    async mounted() {
      this.typologyTree = window.typologyTree
      this.typologyMap = window.typologyMap

      this.typologyTree.forEach(tree => {
        this.$set(this.visibleTypologies, 'code_' + tree.code, true)
      })

      $bus.$on('typologySelected', this.updateSelectedTypology)

      if (location.hash) {
        var typology = location.hash
        typology = typology.replace('#', '')

        if (/^\d{2}(-\d{2})*$/.test(typology) && this.typologyMap[typology]) {
          this.selectedTypology = this.typologyMap[typology]
        }
      }
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

  .typology-browser__query-input {
    width: 50%;
    max-width: 300px;
  }

  .typology-tree__container {
    margin-top: 1rem;
    padding: 0.5rem;
    max-height: calc(100vh - 150px);
  }
</style>
