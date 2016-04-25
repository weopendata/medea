<template>
	<textarea v-model="model" rows="1"></textarea>
</template>

<script>
export default {
  props: ['model'],
  watch: {
    model () {
    	this.$el.style.height = '25px'
    	this.$el.style.height = 1 + Math.min(this.$el.scrollHeight, 400) + 'px'
    }
  }
}
</script>