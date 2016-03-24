<template>
  <div class="devbar ui items horizontal">
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isDetectorist"> Det
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isRegistrar"> Reg
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isFindExpert"> Exp
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isAgency"> Age
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isValidator"> Val
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isResearcher"> Res
      </label>
    </div>
    <div class="item">
      <label>
        <input type="checkbox" v-model="user.isAdmin"> Adm
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
  opacity: .1;
  &:hover {
    opacity: 1;
  }
}
</style>