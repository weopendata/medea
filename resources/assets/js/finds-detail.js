import Vue from 'Vue';
import VueResource from 'vue-resource';
import FindEventDetail from './components/FindEventDetail';
import TopNav from './components/TopNav';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    TopNav,
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
  }
});