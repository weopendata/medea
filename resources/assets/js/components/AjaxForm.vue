<template>
  <form class="form-container ui form">
    <slot>Form expected within ajax-form</slot>
  </form>
</template>

<script>
export default {
  props: ['action', 'submittable'],
  methods: {
    submit (event) {
      event.preventDefault()
      if (!this.submittable) {
        return;
      }
      this.$http.post(this.action, this.$root.find).then(function () {
        console.log('yes!')
      },function () {
        console.log('too bad!')
      })
    }
  },
  attached () {
    this.$el.addEventListener('submit', this.submit, false);
  },
  detached () {
    this.$el.removeEventListener('submit', this.submit, false);
  }
}
</script>

<style lang="sass">
  .ui.form-container {
    margin: 0 auto;
    max-width: 800px;
  }
</style>
