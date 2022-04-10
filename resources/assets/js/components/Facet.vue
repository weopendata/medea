<template>
  <div class="facet" v-if="options">
    <h3 class="facet-title" @click="collapse">
      <i class="ui chevron icon" :class="shown?'down':'right'"></i>
      {{ label }}
    </h3>
    <div class="facet-options" v-if="shown">
      <a
          v-for="(opt, index) in formattedOptions"
          href="#"
          class="facet-a"
          :key="label + '_facet_option_' + index"
          :class="{active: activeOption === opt.value}"
          @click.prevent="toggle(prop, opt.value)"
          :value="opt.value"
          v-text="opt.label"
      />
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
    },
    formattedOptions () {
      return this.options.map(function (option) {
        return typeof option === 'string' ? { label: option, value: option } : option
      })
    }
  },
  methods: {
    collapse () {
      this.$parent.show[this.prop] = !this.shown
    },
    toggle (prop, option) {
      return this.$parent.toggle(prop, option)
    }
  }
}
</script>
