import checkbox from 'semantic-ui-css/components/checkbox.min.js'

import Notifications from './mixins/Notifications'

var email = window.user.email
var id = window.user.id
delete window.user.email

new window.Vue({
  el: #app,
  data () {
    return {
      id: id,
      email: email,
      medeaUser: window.medeaUser,
      message: null,
      roles: window.medeaUser,
      user: window.user,
      submitting: false,
      errors: {}
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
      this.user._token = window.csrf
      this.user.verified = undefined
      this.user.password = undefined
      this.user.email = undefined
      this.user.personType = undefined
      this.user.savedSearches = undefined
      this.$http.put('/persons/' + id, this.user)
      .then(this.submitSuccess, this.submitError).catch(function () {
        this.submitting = false
      })
    },
    submitSuccess (response) {
      this.message = response.data.message
      this.errors = {}
    },
    submitError (response) {
      this.message = response.data.message
      this.errors = response.data
    }
  },
  mounted () {
    $('.ui.checkbox', this.$el).checkbox()
  },
  mixins: [Notifications]
})
