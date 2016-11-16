import TrUser from './components/TrUser'

import Notifications from './mixins/Notifications'

new window.Vue({
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
