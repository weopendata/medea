window.csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

//window.Vue = require('vue');

global.$ = global.jQuery = require('jquery');

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': window.csrf
  }
});

/*window.Vue.http.headers.common['X-CSRF-TOKEN'] = window.csrf;

new Vue({
    el: #app
});*/

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

//require('./bootstrap');

import Vue from 'vue';
import VueResource from 'vue-resource';
import VueTextareaAutosize from "vue-textarea-autosize";

Vue.use(VueResource);
Vue.use(VueTextareaAutosize);

Vue.config.productionTip = false;
Vue.http.headers.common['X-CSRF-TOKEN'] = window.csrf;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('home', require('./components/Home.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});