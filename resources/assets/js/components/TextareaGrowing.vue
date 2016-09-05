<template>
	<textarea v-model="model" rows="2"></textarea>
</template>

<script>
export default {
  props: ['model'],
  watch: {
    model () {
    	this.$el.style.height = '58px'
    	this.$el.style.height = 1 + Math.min(this.$el.scrollHeight, 400) + 'px'
    }
  }
}
</script>