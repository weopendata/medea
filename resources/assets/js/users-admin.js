import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'
import TrUser from './components/TrUser'

import Notifications from './mixins/Notifications'

Vue.use(VueResource)
Vue.config.debug = true

new Vue({
  el: 'body',
  data () {
    return {
      users: window.users && window.users.sort((a, b) => b.id - a.id),
      user: window.medeaUser
    }
  },
  components: {
    TrUser
  },
  mixins: [Notifications]
})
