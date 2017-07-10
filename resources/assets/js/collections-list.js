import Notifications from './mixins/Notifications'

new window.Vue({
  el: 'body',
  data () {
    return {
      find: null,
      user: window.medeaUser
    }
  },
  methods: {
    fetch () {
      // this.$http.get('/api/finds/' + window.initialFind.identifier).then(function (res) {
      //   this.find = res.data
      // }, function () {
      //   console.error('could not fetch findEvent', window.initialFind.identifier)
      // });
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
    FindEventDetail
  }
});
