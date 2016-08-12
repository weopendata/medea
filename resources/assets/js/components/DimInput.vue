<template>
  <div class="ui right labeled input">
    <input type="number" pattern="[0-9]+([\.,][0-9]+)?" v-model.number="dim.value" step="any" min="0">
    <button class="ui button label" @click.prevent.stop="dim.unit=dim.unit==unit?altunit:unit" v-text="dim.unit"></button>
  </div>
</template>

<script>
export default {
  props: {
  	dim: null,
  	unit: {
  		default: 'mm'
  	},
  	altunit: {
  		default: 'cm'
  	}
  }
}
</script>