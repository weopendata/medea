<template>
  <div class="tabs">
    <ul class="tabs__navigation" v-if="tabs.length >= 1">
      <li
              class="tabs__navigation__item"
              :class="{ 'tabs__navigation__item--active': tab === activeTab}"
              v-for="(tab, index) in tabs"
              :key="tab"
              @click="setActiveTab(tab)"
              tabindex="0"
      >
        {{ tab }} <span v-if="tabCounters">{{tabCounters[index] === undefined ? '' : ` (${tabCounters[index]})` }}</span>
      </li>
    </ul>
    <div class="tabs__content">
      <slot :name="activeTab"></slot>
    </div>
  </div>
</template>

<script>
  import $bus from '../helpers/bus'

  export default {
    props: {
      tabs: {
        type: Array,
        default: []
      },
      // use a separate tab counter to prevent problems with url hash matching
      tabCounters: {
        type: Array,
        default: () => []
      },
      saveToUrl: {
        type: Boolean,
        default: false
      }
    },
    data () {
      return {
        activeTab: null
      }
    },
    methods: {
      setActiveTab (tab) {
        this.activeTab = tab

        $bus.$emit('tab', tab);
      }
    },
    mounted () {
      if (this.tabs.length > 0) {
        this.activeTab = this.tabs[0]
      }

      $bus.$emit('tab', this.activeTab)
    }
  }
</script>

<style lang="scss" scoped>
  .tabs__navigation {
    list-style-type: none;
    margin: 0 -10px 20px;
    display: flex;
    justify-content: center;
  }

  .tabs__navigation__item {
    position: relative;
    display: inline-block;
    margin: 0 10px 10px;
    font-size: 16px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: black;
    user-select: none;

    &:after {
      content: "";
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 0;
      height: 2px;
      background-color: #ccc;
      transition: width .2s;
    }

    &.tabs__navigation__item--active {
      &:after {
        background-color: #21BA45;
      }
    }

    &:hover,
    &.tabs__navigation__item--active {
      cursor: pointer;

      &:after {
        width: 100%;
      }
    }
  }

</style>
