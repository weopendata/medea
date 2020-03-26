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

Vue.use(VueResource);

Vue.http.headers.common['X-CSRF-TOKEN'] = window.csrf;


import TextareaGrowing from './components/TextareaGrowing'
import checkbox from 'semantic-ui-css/components/checkbox.min.js'

import Ajax from './mixins/Ajax'
import Notifications from './mixins/Notifications'

new Vue({
  el: '#app',
  data () {
    return {
      show: {
        password: false,
        roles: false
      },
      roles: {
        validator: false,
        detectorist: false,
        onderzoeker: false,
        vondstexpert: false,
        registrator: false
      },
      reg: {
        firstName: '',
        lastName: '',
        email: '',
        password: '',
        expertise: '',
        research: '',
        bio: '',
        passContactInfoToAgency: false,
        showNameOnPublicFinds: false,
        profileAccessLevel: 0
      },
      oeerk: {
        jjjj: null,
        nnnnn: null,
      },
      score: 0,
      errors: {},
      submitAction: 'register',
      registered: false,
      user: window.medeaUser
    }
  },
  computed: {
    biografieLabel() {
      return [
        this.roles.detectorist && 'Biografie',
        (this.roles.vondstexpert || this.roles.registrator) && 'Projectachtergrond'
      ].filter(Boolean).join('/')
    },
    biografiePlaceholder () {
      return [
        this.roles.detectorist && 'Schrijf hier kort iets over jezelf (optioneel).',
        (this.roles.vondstexpert || this.roles.registrator) && 'Beschrijf hier kort je project, collectie of wetenschappelijke achtergrond.'
      ].filter(Boolean).join('\n')
    },
    submittable () {
      if (this.score < 2) {
        return false
      }
      if (this.roles.onderzoeker && !this.roles.vondstexpert) {
        this.roles.onderzoeker = false
      }
      if (!this.registered && this.reg.firstName && this.reg.lastName && this.reg.email && this.reg.password) {
        if (this.roles.vondstexpert && !this.reg.expertise) {
          return false
        }
        if (this.roles.onderzoeker && (!this.reg.research || !this.reg.affiliation || !this.reg.function)) {
          return false
        }
        for (let key in this.roles) {
          if (this.roles[key]) {
            return true
          }
        }
      }
      return false
    }
  },
  methods: {
    pwFeedback () {
      var score = zxcvbnAsync(this.reg.password)
      if (score === -1) {
        return
      }
      this.score = score
      if (this.reg.password.length < 6 && this.score > 2) {
        this.score = 2
      } else {
        this.reg.passwordRegErrors = []
      }
    },
    formdata () {
      var data = this.reg

      data.personType = []

      for (let type in this.roles) {
        if (this.roles[type]) {
          data.personType.push(type)
        }
      }

      if (this.oeerk.jjjj) {
        data.oeerk = 'OE/ERK/Metaaldetectorist/' + this.oeerk.jjjj + '/' + this.oeerk.nnnnn
      }
      return data
    },
    submitSuccess () {
      this.registered = true
      this.errors = {}
    },
    submitError (res) {
      this.errors = res.data
    }
  },
  mounted () {
    $('.ui.checkbox').checkbox()
    $('nav .ui.dropdown').dropdown()
  },
  mixins: [Ajax, Notifications],
  components: {
    TextareaGrowing
  }
})

var zxcvbnAsync = function(pw) {
  if (window.zxcvbn) {
    return window.zxcvbn(pw).score
  }
  if (window.zxcvbnLoading) {
    return -1
  }
  window.zxcvbnLoading = true
  var first, s
  s = document.createElement('script')
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.3.0/zxcvbn.js'
  s.type = 'text/javascript'
  s.async = true
  first = document.getElementsByTagName('script')[0]
  first.parentNode.insertBefore(s, first)
  return -1
}

window.startIntro = function () {
  if (!window.medeaUser.email) {
    introJs()
    .setOption('hideNext', true)
    .setOption('hidePrev', true)
    .setOption('skipLabel', 'Registreren')
    .setOption('doneLabel', 'Registreren')
    .start()
    .oncomplete(function() {
      window.location.href = '#register';
    })
  } else {
    document.getElementById('findsCreate').setAttribute('href', '/finds/create?startIntro')
    introJs()
    .setOption('hideNext', true)
    .setOption('hidePrev', true)
    .setOption('skipLabel', 'Nieuwe vondst registreren')
    .setOption('doneLabel', 'Nieuwe vondst registreren')
    .start()
    .oncomplete(function() {
      window.location.href = '/finds/create?startIntro';
    })
  }
}
