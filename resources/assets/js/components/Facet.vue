<template>
  <div class="facet" v-if="options">
    <h3 class="facet-title" @click="collapse"><i class="ui chevron icon" :class="shown?'down':'right'"></i> {{ label }}</h3>
    <div class="facet-options" v-if="shown">
      <a href="#" class="facet-a" :class="{active:activeOption==opt}" @click.prevent="toggle(prop, opt)" v-for="opt in options" v-text="opt"></a>
    </div>
  </div>
</template>

<script>
export default {
  props: ['prop', 'options', 'label'],
  computed: {
    shown () {
      return this.$parent.show[this.prop]
    },
    activeOption () {
      return this.$parent.model[this.prop]
    }
  },
  methods: {
    collapse () {
      this.$parent.show[this.prop] = !this.shown
    },
    toggle (p, o) {
      return this.$parent.toggle(p, o)
    }
  }
}
</script>
