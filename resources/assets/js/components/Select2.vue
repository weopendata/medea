<template>
  <select>
    <slot></slot>
  </select>
</template>

<script>
export default {
  props: ['options', 'value'],
  attached () {
    if (!window.$) {
      return
    }
    const $elem = $(this.$el)
      .select2(this.options)
      // emit event on change.
      .on('select2:select', (ev) => {
        this.$emit('select', this.$el.value)
        $elem.val('').trigger('change')
      })
  },
  destroyed () {
    $(this.$el).off().select2('destroy')
  }
}
</script>
