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
      users: window.users.sort((a, b) => b.id - a.id),
      user: window.medeaUser
    }
  },
  components: {
    TrUser
  }
})
