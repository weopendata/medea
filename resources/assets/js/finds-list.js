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
      finds: [],
      user: window.medeaUser
    }
  },
  ready () {
    this.$http.get('/api-mock/finds.json').then(function (res) {
      this.finds = res.data
    }, function () {
      console.error('could not find findevents')
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