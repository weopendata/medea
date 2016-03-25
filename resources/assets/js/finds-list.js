import Vue from 'vue/dist/vue.min.js';
import VueResource from 'vue-resource/dist/vue-resource.min.js';
import FindsList from './components/FindsList';
import FindsFilter from './components/FindsFilter';
import DevBar from './components/DevBar';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    DevBar,
    FindsFilter,
    FindsList
  },
  data () {
    return {
      finds: window.initialFinds || [],
      filterState: window.filterState || {myfinds: false},
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
    fetch (query) {
      query = query || ''
      this.$http.get('/api/finds?' + query).then(function (res) {
        this.finds = res.data.finds
        this.filterState = res.data.filterState
        window.history.pushState({}, document.title, '?' + query)
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