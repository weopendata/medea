import Vue from 'vue/dist/vue.min.js';
import VueResource from 'vue-resource/dist/vue-resource.min.js';

import dropdown from 'semantic-ui-css/components/dropdown.min.js';
import transition from 'semantic-ui-css/components/transition.min.js';

import FindEventDetail from './components/FindEventDetail';
import DevBar from './components/DevBar';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
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
  }
});