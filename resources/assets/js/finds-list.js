import Vue from 'vue/dist/vue.min.js';
import VueResource from 'vue-resource/dist/vue-resource.min.js';
import FindsList from './components/FindsList';
import DevBar from './components/DevBar';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    DevBar,
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