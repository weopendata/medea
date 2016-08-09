<template>
  <label class="fe-check" :class="{'fe-invalid':!$parent.feedback[prop]}"><input type="checkbox" v-model="$parent.feedback[prop]"></label>
</template>

<script>
export default {
	props: ['prop']
}
</script>