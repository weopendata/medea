import Vue from 'Vue';
import VueResource from 'vue-resource';
import FindsList from './components/FindsList';
import TopNav from './components/TopNav';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    TopNav,
    FindsList
  },
  data () {
    return {
      finds: window.initialFinds || [],
      user: window.medeaUser
    }
  },
  ready () {
    console.log(JSON.parse(JSON.stringify(window.initialFinds)))
    if (!this.finds || !this.finds.length) {
      this.fetch()
    }
  },
  methods: {
    fetch () {
      this.$http.get('/api/finds').then(function (res) {
        this.finds = res.data
      }, function () {
        console.error('could not fetch findevents')
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