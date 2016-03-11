<template>
  <div class="ui secondary green pointing menu">
    <a href="" class="item {{path==='/'?'active':''}}"><i class="home icon"></i></a>
    <a href="/finds" class="item {{path==='/finds'?'active':''}}">Vondsten</a>
    <a href="/finds/create" class="item {{path==='/finds/create'?'active':''}}">Nieuwe vondst</a>
    <label class="item">
      <input type="checkbox" v-model="user.isDetectorist"> Det
    </label>
    <label class="item">
      <input type="checkbox" v-model="user.isRegistrar"> Reg
    </label>
    <label class="item">
      <input type="checkbox" v-model="user.isFindExpert"> Exp
    </label>
    <label class="item">
      <input type="checkbox" v-model="user.isAgency"> Age
    </label>
    <label class="item">
      <input type="checkbox" v-model="user.isResearcher"> Res
    </label>
    <label class="item">
      <input type="checkbox" v-model="user.isAdmin"> Adm
    </label>
    <a href="login" class="right floated item {{path==='/login'?'active':''}}">Log in</a>
  </div>
</template>

<script>
export default {
  props: ['user'],
  data () {
    return {
      path: window.location.pathname
    }
  }
}
</script>