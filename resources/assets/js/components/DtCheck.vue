<template>
  <label class="fe-check" :class="{'fe-valid':!$parent.feedback[prop]}">
  	<i :class="$parent.feedback[prop] ? 'close icon' : 'checkmark icon'"></i>
  	<input type="checkbox" v-model="$parent.feedback[prop]" style="display:none">
  </label>
</template>

<script>
export default {
	props: ['prop']
}
</script>