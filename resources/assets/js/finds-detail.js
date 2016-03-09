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
    this.$http.get('/api-mock/find15.json').then(function (res) {
      this.find = res.data
    }, function () {
      console.error('could not find findEvent 15')
    });
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