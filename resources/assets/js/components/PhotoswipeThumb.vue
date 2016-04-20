<template>
  <img :src="src.resized" @click="trigger(index)">
</template>

<script>
export default {
  props: ['src', 'index'],
  methods: {
    trigger () {
      if (!window.PhotoSwipe) {
        return console.warn('PhotoSwipe missing')
      }
      var el = this.$el
      this.$dispatch('initPhotoswipe', {
        index: parseInt(this.index),
        getThumbBoundsFn () {
          var pageYScroll = window.pageYOffset || document.documentElement.scrollTop; 
          var rect = el.getBoundingClientRect();
          return {
            x: rect.left,
            y: rect.top + pageYScroll,
            w: rect.width
          };
        }
      })
    }
  },
}
</script>