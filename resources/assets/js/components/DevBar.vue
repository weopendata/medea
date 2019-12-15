<template>
  <div class="devbar ui items horizontal">
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.detectorist"> Det
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.registrator"> Reg
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.vondstexpert"> Exp
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isAgency"> Age
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.validator"> Val
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.onderzoeker"> Res
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.administrator"> Adm
      </label>
    </div>
  </div>
</template>

<script>
export default {
  props: ['user']
}
</script>

<style lang="scss">
.devbar {
  position: relative;
  z-index: 2;
  display: inline-block;
  opacity: 0;
  background: white;
  &:hover {
    opacity: 1;
  }
}
</style>