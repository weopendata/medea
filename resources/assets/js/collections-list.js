import Notifications from './mixins/Notifications'
import CollectionsList from './components/CollectionsList'
import parseLinkHeader from 'parse-link-header'

// Parse link header
function getPaging (header) {
  if (typeof header === 'function') {
    return parseLinkHeader(header('link')) || {}
  }
  if (typeof header === 'string') {
    return parseLinkHeader(header) || {}
  }
  return header && header.map && header.map.Link && parseLinkHeader(header.map.Link[0]) || {}
}

new window.Vue({
  el: 'body',
  data () {
    return {
      paging: getPaging(window.link),
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
