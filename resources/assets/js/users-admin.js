import TrUser from './components/TrUser'

import Notifications from './mixins/Notifications'

new window.Vue({
  el: #app,
  data () {
    return {
      users: window.users,
      user: window.medeaUser
    }
  },
  components: {
    TrUser
  },
  mixins: [Notifications]
})
