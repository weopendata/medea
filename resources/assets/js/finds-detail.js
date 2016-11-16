import FindEventDetail from './components/FindEventDetail'
import DevBar from './components/DevBar'

import Notifications from './mixins/Notifications'

new window.Vue({
  el: 'body',
  components: {
    DevBar,
    FindEventDetail
  },
  data () {
    return {
      find: null,
      user: window.medeaUser
    }
  },
  ready () {
    console.log(JSON.parse(JSON.stringify(window.initialFind)))
    if (window.initialFind && window.initialFind.identifier) {
      this.find = window.initialFind;
    } else {
      this.fetch()
    }
  },
  methods: {
    fetch () {
      this.$http.get('/api/finds/' + window.initialFind.identifier).then(function (res) {
        this.find = res.data
      }, function () {
        console.error('could not fetch findEvent', window.initialFind.identifier)
      });
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
  mixins: [Notifications]
});