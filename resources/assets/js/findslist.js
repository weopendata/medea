import Vue from 'Vue';
import VueResource from 'vue-resource';
import FindsList from './components/FindsList.vue';

Vue.use(VueResource)
new Vue({
  el: 'body',
  components: {
    FindsList
  }
});