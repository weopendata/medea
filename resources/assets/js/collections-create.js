import CollectionsCreate from './components/CollectionsCreate.vue'

import Notifications from './mixins/Notifications'

new window.Vue({
  el: 'body',
  data () {
    return {
      find: null,
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
    CollectionsCreate
  }
});
