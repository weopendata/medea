<template>
  <select>
    <slot></slot>
  </select>
</template>

<script>
let loaded = false
let ready = false

export default {
  props: ['options', 'value'],
  methods: {
    load () {
      if (loaded) {
        return this.loaded()
      }
      loaded = true
      
      var linkElem = document.createElement('link');
      document.getElementsByTagName('head')[0].appendChild(linkElem);
      linkElem.rel = 'stylesheet';
      linkElem.type = 'text/css';
      linkElem.href = 'https://unpkg.com/select2@4.0.3/dist/css/select2.min.css';

      var first, s
      s = document.createElement('script')
      s.onload = () => {
        ready = true
        this.loaded()
      }
      s.onerror = () => {
        console.warn('could not load select2')
      }
      s.type = 'text/javascript'
      s.async = true
      s.src = 'https://unpkg.com/select2@4.0.3'
      first = document.getElementsByTagName('script')[0]
      first.parentNode.insertBefore(s, first)
    },
    loaded () {
      if (!ready) {
        setTimeout(() => this.loaded(), 1000)
        return
      }
      console.log('build select', loaded, ready)
      const $elem = $(this.$el)
        .select2(this.options)
        // emit event on change.
        .on('select2:select', (ev) => {
          this.$emit('select', this.$el.value)
          $elem.val('').trigger('change')
        })
    }
  },
  attached () {
      console.log('attach select', loaded, ready)
    this.load()
  },
  destroyed () {
    $(this.$el).off().select2('destroy')
  }
}
</script>
