<template>
  <img :src="image.resized" @click="trigger(index)" :title="'Foto '+(image.identifier||'nieuw')">
</template>

<script>
export default {
  props: ['image', 'index'],
  methods: {
    trigger () {
      if (!window.PhotoSwipe) {
        return console.warn('PhotoSwipe missing')
      }
      var el = this.$el;

      this.$emit('initPhotoswipe', {
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