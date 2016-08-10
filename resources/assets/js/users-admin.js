import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'
import TrUser from './components/TrUser'

import parseLink from 'parse-link-header'

Vue.use(VueResource)
Vue.config.debug = true

new Vue({
  el: 'body',
  data () {
    return {
      users: window.users,
      user: window.medeaUser
    }
  },
  ready () {
  },
  components: {
    TrUser
  }
})
