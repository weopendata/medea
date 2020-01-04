<template>
  <div class="ui right labeled input">
    <input type="number" :value="inputValue" @blur="update" @input="update" step="1" max="2022" :placeholder="placeholder||'Vul jaartal in'">
    <button class="ui button label" @click.prevent.stop="model=-model" v-text="model<0?'v.C.':'n.C.'" data-tooltip="Klik om te wisselen tussen 'voor Christus' en 'na Christus'" data-position="top right" data-green=""></button>
  </div>
</template>

<script>
  export default {
    props: ['model', 'placeholder'],
    computed: {
      inputValue () {
        return this.model;
      }
    },
    methods: {
      update (evt) {
        this.$emit("update", evt.target.value)
      }
    }
  }
</script>