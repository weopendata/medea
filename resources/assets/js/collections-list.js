import Notifications from './mixins/Notifications'
import CollectionsList from './components/CollectionsList'

new window.Vue({
  el: 'body',
  data () {
    return {
      collections: window.initialCollections || []
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
  /* console.log(JSON.parse(JSON.stringify(window.initialFind)))
    if (window.initialFind && window.initialFind.identifier) {
      this.find = window.initialFind;
    } else {
      this.fetch()
    }*/
  },
  watch: {
    /*'user': {
      deep: true,
      handler (user) {
        localStorage.debugUser = JSON.stringify(user) 
      }
    }*/
  },
  mixins: [Notifications],
  components: {
    CollectionsList
  }
});
