<template>
  <form @submit.prevent="submit" class="ui register-container form">
    <div class="ui dimmer modals page transition visible active">
      <div class="ui modal transition visible active">
        <div class="header">
          <h2>Nodig nieuwe gebruiker uit</h2>
        </div>
        <div class="content">
          <div class="two fields">
            <div class="field" :class="{error:errors.firstName}">
              <label for="firstName">Voornaam</label>
              <input v-model="reg.firstName" type="text" id="firstName">
              <div v-for="msg in errors.firstName" v-text="msg" class="input"></div>
            </div>
            <div class="field" :class="{error:errors.lastName}">
              <label for="lastName">Achternaam</label>
              <input v-model="reg.lastName" type="text" id="lastName">
              <div v-for="msg in errors.lastName" v-text="msg" class="input"></div>
            </div>
          </div>
          <div class="field" :class="{error:errors.email}">
            <label for="email">Email</label>
            <input v-model="reg.email" type="email" id="email">
            <div v-for="msg in errors.email" v-text="msg" class="input"></div>
          </div>

          <div class="field pw-strength-fit" :class="{error:errors.password}">
            <label for="pw" @click="show.password=!show.password">Wachtwoord <a href="#" @click.prevent
                                                                                style="color:#999;font-weight:normal"
                                                                                v-text="show.password?'verbergen':'tonen'">tonen</a></label>
            <input v-model="reg.password" :type="show.password?'text':'password'" id="pw" @input="pwFeedback">
            <div :class="'pw-strength pw-strength' + score">
              <div class="pw-strength-line"></div>
            </div>
            <div v-for="msg in errors.password" v-text="msg" class="input"></div>
            <div class="help-box">
              Een sterk wachtwoord kan bijvoorbeeld een zin zijn van 4 woorden. Er worden geen verplichte karakters
              verwacht zoals symbolen of hoofdletters.
            </div>
          </div>
        </div>
        <div class="field" style="margin-top: 1rem;">
          <button type="submit" class="ui button" :class="submittable ? 'green' : ''" :disabled="!submittable">Registreer
          </button>
          <button class="ui button" @click="close()">{{registered ? 'Overzicht' : 'Annuleer'}}
          </button>
        </div>
        <div v-if="registered" style="color:#090">
          <h3>De gebruiker werd uitgenodigd en krijgt hiervan een mail ter bevestiging.</h3>
        </div>
      </div>
    </div>
  </form>
</template>

<script>
  var zxcvbnAsync = function (pw) {
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

  export default {
    name: "InviteUser",
    data() {
      return {
        show: {
          password: false
        },
        reg: {
          firstName: '',
          lastName: '',
          email: '',
          password: '',
        },
        score: 0,
        errors: {},
        submitAction: 'register',
        registered: false,
        user: null
      }
    },
    computed: {
      submittable () {
        return this.reg.firstName.length
          && this.reg.lastName.length
          && this.reg.email.length
          && this.score >= 2
      }
    },
    methods: {
      close () {
        this.$emit('close')
      },
      submit () {
        axios
          .post('/api/administrators', this.reg)
          .then(response => {
            this.registered = true
            this.reg = {
              firstName: '',
              lastName: '',
              email: '',
              password: '',
            }
            this.errors = {}
          })
          .catch(error => {
            this.errors = error.data
          })
      },
      pwFeedback() {
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
      }
    }
  }
</script>

<style scoped>

</style>
