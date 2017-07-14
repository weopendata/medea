import UserCollections from './components/UserCollections'

import Notifications from './mixins/Notifications'

new window.Vue({
  el: '#app',
  data () {
    return {
      user: window.medeaUser
    }
  },
  components: {
    UserCollections
  },
  mixins: [Notifications]
})
