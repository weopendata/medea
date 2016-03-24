<template>
	<textarea v-model="model" :rows="rows"></textarea>
</template>

<script>
export default {
  props: ['model'],
  computed: {
    rows () {
    	return 3 + Math.floor(this.model.length / 50)
    }
  }
}
</script>