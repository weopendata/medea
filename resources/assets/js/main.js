window.csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

global.$ = global.jQuery = require('jquery');

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': window.csrf
  }
});

window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

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

Vue.component('nav-bar', require('./components/NavBar.vue').default);
Vue.component('home', require('./components/Home.vue').default);
Vue.component('users-overview', require('./components/UsersOverview.vue').default);
Vue.component('finds-overview', require('./components/FindsOverview.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});