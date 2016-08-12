import Vue from 'vue/dist/vue.min.js'
import VueResource from 'vue-resource/dist/vue-resource.min.js'
import checkbox from 'semantic-ui-css/components/checkbox.min.js'

Vue.use(VueResource)
Vue.config.debug = true

var email = window.user.email
var id = window.user.id
delete window.user.email

new Vue({
  el: 'body',
  data () {
    return {
      id: id,
      email: email,
      medeaUser: window.medeaUser,
      message: null,
      roles: window.medeaUser,
      user: window.user,
      submitting: false,
    }
  },
  computed: {
    detectoristArray () {
      return this.user && this.user.detectoristNumber && this.user.detectoristNumber.split('/') || ['OE', 'ERK', 'Metaaldetectorist', '', '']
    },
    jjjj: {
      get () {
        return this.detectoristArray[3]
      },
      set (v) {
        this.setDetectoristNumber(3, v)
      }
    },
    nnnnn: {
      get () {
        return this.detectoristArray[4]
      },
      set (v) {
        this.setDetectoristNumber(4, v)
      }
    }
  },
  methods: {
    setDetectoristNumber (i, v) {
      var a = this.detectoristArray
      a[i] = v
      if (a[3] || a[4]) {
        this.$set('user.detectoristNumber', a.join('/'))
      } else {
        this.$set('user.detectoristNumber', null)
      }
    },
    submit () {
      this.user._token = 'PUT'
      this.user.password = undefined
      this.user.email = undefined
      this.user.personType = undefined
      this.$http.put('/users/' + this.id, this.user)
      .then(this.submitSuccess, this.submitError).catch(function () {
        this.submitting = false
      })
    },
    submitSuccess (response) {
      this.message = response.data.message
    },
    submitError () {
      alert('failed to save')
    }
  },
  attached () {
    $('.ui.checkbox', this.$el).checkbox()
  }
})
