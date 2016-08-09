<template>
  <label class="fe-check" :class="{'fe-invalid':!$parent.validation[prop]}"><input type="checkbox" v-model="$parent.validation[prop]"></label>
</template>

<script>
export default {
	props: ['prop']
}
</script>