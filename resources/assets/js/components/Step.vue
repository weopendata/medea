<template>
  <div class="step" :class="{active:active, muted:!active, ready:ready}" :id="'step'+number">
  	<h3 @click="click">{{number}}. {{title}}</h3>
    <slot>Content expected</slot>
  </div>
</template>

<script>
export default {
	props: ['number', 'title'],
	computed: {
		active () {
			return this.$parent.step == this.number
		},
		ready () {
			return this.$parent.ready[this.number]
		}
	},
	methods: {
		click () {
			this.$parent.step = this.number
		}
	}
}
</script>