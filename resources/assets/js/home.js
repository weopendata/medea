import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'
import DevBar from './components/DevBar'
import TextareaGrowing from './components/TextareaGrowing'
import checkbox from 'semantic-ui-css/components/checkbox.min.js'

import Ajax from './mixins/Ajax'
import Notifications from './mixins/Notifications'

Vue.use(VueResource)
Vue.config.debug = true
new Vue({
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
        expert: false,
        registrator: false
      },
      user: {
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
      registered: false
    }
  },
  computed: {
    submittable () {
      if (this.score < 2) {
        return false
      }
      if (this.roles.onderzoeker && !this.roles.vondstexpert) {
        this.roles.onderzoeker = false
      }
      if (!this.registered && this.user.firstName && this.user.lastName && this.user.email && this.user.password) {
        if (this.roles.vondstexpert && !this.user.expertise) {
          return false
        }
        if (this.roles.onderzoeker && (!this.user.research || !this.user.affiliation || !this.user.function)) {
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
      var score = zxcvbnAsync(this.user.password)
      if (score === -1) {
        return
      }
      this.score = score
      if (this.user.password.length < 6 && this.score > 2) {
        this.score = 2
      } else {
        this.user.passwordRegErrors = []
      }
    },
    formdata () {
      var data = this.user

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
  ready () {
    $('.ui.checkbox').checkbox()
  },
  el: 'body',
  mixins: [Ajax, Notifications],
  components: {
    TextareaGrowing,
    DevBar
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
