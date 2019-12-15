import Collection from './components/Collection.vue'

import Notifications from './mixins/Notifications'

new window.Vue({
  el: #app,
  data () {
    return {
      collection: window.initialCollection,
      user: window.medeaUser
    }
  },
  watch: {
    'user': {
      deep: true,
      handler (user) {
        localStorage.debugUser = JSON.stringify(user)
      }
    }
  },
  mixins: [Notifications],
  components: {
    Collection
  }
});
