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
        <input type="checkbox" v-model="user.expert"> Exp
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
        <input type="checkbox" v-model="user.admin"> Adm
      </label>
    </div>
  </div>
</template>

<script>
export default {
  props: ['user']
}
</script>

<style lang="sass">
.devbar {
  position: relative;
  z-index: 2;
  opacity: 0;
  &:hover {
    opacity: 1;
  }
}
</style>