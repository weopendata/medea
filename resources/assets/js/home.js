import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'
import DevBar from './components/DevBar'
import TextareaGrowing from './components/TextareaGrowing'
import checkbox from 'semantic-ui-css/components/checkbox.min.js'

import Ajax from './mixins/Ajax'

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
      errors: {},
      submitAction: 'register',
      registered: false
    }
  },
  computed: {
    submittable () {
      if (!this.registered && this.user.firstName && this.user.lastName && this.user.email && this.user.password) {
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
  mixins: [Ajax],
  components: {
    TextareaGrowing,
    DevBar
  }
});
