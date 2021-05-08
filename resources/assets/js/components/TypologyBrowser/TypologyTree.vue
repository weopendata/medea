<template>
  <div class="tree-root" :style="treeStyle">
      <div class="item-line">
        <div @click="handleToggleCollapsed">{{root.label}}&nbsp;[{{root.code}}]</div>
        <div class="ui mini icon pull-right" @click.prevent="selectTypology">
          <i class="eye icon"></i>
        </div>
      </div>

    <template v-if="!this.shouldCollapse && children && children.length > 0">
      <div class="children">
        <typology-tree
                v-for="(child, index) in children"
                :root="child"
                :key="'typology__' + child.code"
                :searchQuery="searchQuery"
                @childMatchesQuery="updateChildMatchesQuery"
                v-if="child.code"
        />
      </div>
    </template>
  </div>
</template>

<script>
  import $bus from "../../helpers/bus";

  export default {
    name: "TypologyTree",
    props: {
      root: Object,
      searchQuery: {
        type: String,
        default: () => ''
      }
    },
    data () {
      return {
        collapsed: true,
        childMatchesSearchQuery: false,
        searchQueryCache: ''
      }
    },
    computed: {
      treeStyle () {
        if (this.displayItem) {
          return {}
        }

        return {visibility: 'hidden', height: '0px', border: '0px', padding: '0px'}
      },
      children () {
        if (! this.root.childrenCodes) {
          return []
        }

        var codes = Object.values(this.root.childrenCodes)

        return codes
      },
      shouldCollapse () {
        if (this.searchQuery && this.searchQuery.length >= 2) {
          return false
        }

        return this.collapsed
      },
      displayItem () {
        if (!this.searchQuery || this.searchQuery.length < 2) {
          return true
        }

        var regex = new RegExp(`.*${this.searchQuery}.*`, 'ig')

        if (regex.test(this.root.label) || regex.test(this.root.code)) {
          this.$emit('childMatchesQuery', { match: true, code: this.root.code })

          return true
        }

        this.$emit('childMatchesQuery', { match: this.childMatchesSearchQuery, code: this.root.code })

        return this.childMatchesSearchQuery
      },
    },
    methods: {
      handleToggleCollapsed () {
        this.collapsed = !this.collapsed
      },
      updateChildMatchesQuery (matchesQuery) {
        if (matchesQuery.match) {
          this.childMatchesSearchQuery = matchesQuery.match
        }
      },
      selectTypology () {
        $bus.$emit('typologySelected', { typology: this.root })
      }
    },
    watch: {
      searchQuery (v) {
        this.childMatchesSearchQuery = false
      }
    }
  }
</script>

<style lang="scss" scoped>
  .hiddenItem {
    visibility: hidden;
  }

  .children {
    padding-left: 1rem;
    border-left: 1px solid rgba(0, 0, 0, .5);
    -webkit-background-clip: padding-box;
    background-clip: padding-box;
    margin-left: 0.15rem;
  }
  .tree-root {
    border: 1px solid transparent;
    padding-bottom: 5px;
    user-select: none;
  }
  .accepting-drop {
    border: 1px solid #00C3AF;
    > .item-line > .name-span {
      color: #00C3AF;
    }
  }
  .name-input {
    font-size: inherit;
    padding: 0 6px;
    position: relative;
    left: -7px;
    width: 300px;
  }
  .hidden {
    visibility: hidden;
  }
  .item-line {
    display: flex;
    height: 100%;
    overflow: auto;
    line-height: 24px;
    justify-content: space-between;
  }
  .item-line {
    .buttons {
      display: none;
    }
    &:hover {
      background-color: #f3f3f3;
      .buttons {
        display: block;
      }
    }
    label {
      display: flex;
      margin-top: 3px;
    }
  }
  .icon {
    cursor: pointer;
    opacity: 0.5;
    &:hover {
      opacity: 1;
    }
  }
  .tree__item-description {
    margin-left: 10px;
    color: #aaa;
    font-size: 85%;
    margin-bottom: 10px;
    margin-top: -3px;
  }
  .tree__main-item-description {
    font-weight: 500;
  }
  .tree__item-cta {
    font-size: 10px;
    line-height: 10px;
    padding: 3px;
    height: fit-content;
    top: 0.3em;
    margin-left: 0.8em;
  }
</style>
