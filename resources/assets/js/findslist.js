var Vue = require('vue');

import Findslist from './components/FindsList.vue';

new Vue({

  el: '#app',

  components : {
    Findslist
  },

  ready () {
    // Fetch data and fill it in the finds list component
  }
});