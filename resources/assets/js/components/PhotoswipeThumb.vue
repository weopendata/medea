<template>
    <img :src="imageLink" @click="trigger(index)" :title="'Foto '+(image.identifier||'nieuw')">
</template>

<script>
export default {
  props: ['image', 'index'],
  computed: {
    imageLink () {
      return this.image.src ? this.image.src : this.image.resized
    }
  },
  methods: {
    trigger () {
      if (!window.PhotoSwipe) {
        return console.warn('PhotoSwipe missing')
      }

      if (this.image.src) {
        window.open(this.image.src, '_blank')

        return
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
