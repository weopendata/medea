<template>
	<textarea @blur="update" @input="update" :value="value" rows="2"></textarea>
</template>

<script>
export default {
  props: ['model'],
  computed: {
    value () {
        return this.model;
    }
  },
  methods: {
    update (evt) {
        this.$emit('input', evt.target.value);
    }
  },
  watch: {
    model () {
    	this.$el.style.height = '58px'
    	this.$el.style.height = 1 + Math.min(this.$el.scrollHeight, 400) + 'px'
    }
  }
}
</script>