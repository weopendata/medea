import Vue from 'Vue';
import VueResource from 'vue-resource';
import TopNav from './components/TopNav';
import AjaxForm from './components/AjaxForm';
import Step from './components/Step';

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
  el: 'body',
  components: {
    TopNav,
    AjaxForm,
    Step
  },
  data () {
    return {
      step: 2,
      user: window.medeaUser
    }
  },
  methods: {
    toStep (i) {
      this.step = i
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