<template>
  <div class="ui right labeled input">
    <input type="number" v-model="model" step="1" max="2020" :placeholder="placeholder||'Vul jaartal in'">
    <button class="ui button label" @click.prevent.stop="model=-model" v-text="model<0?'v.C.':'n.C.'"></button>
  </div>
</template>

<script>
export default {
  props: ['model', 'placeholder']
}
</script>