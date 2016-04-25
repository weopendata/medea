<template>
  <div class="ui right labeled input">
    <input type="number" v-model="dim.value">
    <button class="ui button label" @click.prevent.stop="dim.unit=dim.unit==unit?altunit:unit" v-text="dim.unit"></button>
  </div>
</template>

<script>
export default {
  props: {
  	dim: {},
  	unit: {
  		default: 'cm'
  	},
  	altunit: {
  		default: 'mm'
  	}
  }
}
</script>