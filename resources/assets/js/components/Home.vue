<template>
  <div>
    <div class="hero">
      <form method="GET" action="/finds" class="ui container">
        <p class="lead" data-step="1" data-intro="Deze intro zal je begeleiden bij het registreren van een vondst.">Welkom op het <strong>MEDEA</strong> platform, een community platform dat experten, onderzoekers en detectoristen samenbrengt.</p>
        <p v-if="stats && stats.validatedFinds">
          Verken <b>{{ stats.validatedFinds }}</b> metaalvondsten die we samen in Vlaanderen in kaart brachten
        </p>
        <p>
          <span class="ui action input" style="width: 30%;">
            <input type="text" placeholder="Search..." name="query">
            <button class="ui blue icon button">
              <i class="search icon"></i>
            </button>
          </span>
        </p>
        <p>
          <template v-if="! user">
            <a href="/login" class="ui green button"><i class="sign in icon"></i> Log in </a>
            <a href="#register" class="ui green button"><i class="edit icon"></i> Word lid </a>
          </template>
          <template v-else>
            <button type="submit" class="ui green button"><i class="sign in icon"></i> Vondsten doorzoeken</button>
          </template>
        </p>
      </form>
    </div>
    <form @submit.prevent="submit" class="ui register-container form">
      <template v-if="! user">
        <div data-step="2" data-intro="Ben je al geregistreerd? Klik dan op 'Login'.<br><br>Als je nog niet geregistreerd bent op MEDEA, vul dan dit formulier in en klik op 'Registreer'.">
          <a href="/login?startIntro" style="float:right" class="ui basic small button">Login</a>
          <h2 id="register">Registreren</h2>
        </div>
      </template>
      <template v-else>
        <div>
          <a href="/finds/create?startIntro" style="float:right" class="ui basic small button">Nieuwe vondst</a>
          <h2 id="register">Registreren</h2>
        </div>
      </template>

      <h3>Contactgegevens</h3>
      <div class="ui message">
        <p>
          Vul hier je naam en emailadres in, en kies een wachtwoord om in te loggen. Deze gegevens zullen niet gedeeld worden met derden zonder je expliciete toestemming (zie sectie ‘Privacy’ onderaan).
        </p>
      </div>
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
        <label for="pw" @click="show.password=!show.password">Wachtwoord <a href="#" @click.prevent style="color:#999;font-weight:normal" v-text="show.password?'verbergen':'tonen'">tonen</a></label>
        <input v-model="reg.password" :type="show.password?'text':'password'" id="pw" @input="pwFeedback">
        <div :class="'pw-strength pw-strength' + score">
          <div class="pw-strength-line"></div>
        </div>
        <div v-for="msg in errors.password" v-text="msg" class="input"></div>
        <div class="help-box">
          Een sterk wachtwoord kan bijvoorbeeld een zin zijn van 4 woorden. Er worden geen verplichte karakters verwacht zoals symbolen of hoofdletters,de sterkte wordt berekend op hoe uiteenlopend het wachtwoord is samengesteld.
        </div>
      </div>

      <h3>Rollen</h3>
      <p>Welke rol(len) wil je opnemen op het MEDEA platform?</p>
      <div class="field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" v-model="roles.detectorist">
          <label>
            <b>Detectorist</b>
            <br>Je kan je eigen vonsten documenteren en publiceren.
          </label>
        </div>
      </div>
      <div class="field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" v-model="roles.registrator">
          <label>
            <b>Registrator</b>
            <br>Je kan vondsten van anderen documenteren en publiceren in het kader van een bestaande collectie (bijv. van een heemkundige kring) of project. Beschrijf deze collectie of dit project kort in het veld ‘project’ hieronder. Een MEDEA-medewerker neemt contact met je op om concrete afspraken te maken.
          </label>
        </div>
      </div>
      <div class="field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" v-model="roles.validator">
          <label>
            <b>Validator</b>
            <br>Je bent bereid om je als vrijwilliger in te zetten voor de vlotte werking van MEDEA. Je gaat na of gemelde vondstfiches in orde zijn voor publicatie. Een MEDEA-medewerker zal contact met je opnemen om concrete afspraken te maken.
          </label>
        </div>
      </div>
      <div class="field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" v-model="roles.vondstexpert">
          <label>
            <b>Vondstexpert</b>
            <br>Je hebt expertise op basis van praktijk- en/of onderzoekservaring die je wil inzetten om vondsten te bestuderen en classificeren.
          </label>
        </div>
      </div>
      <div class="required field" v-if="roles.vondstexpert">
        <label for="expertise">Expertise</label>
        <textarea-autosize style="min-height: 60px" id="expertise" v-model="reg.expertise" placeholder="Schrijf iets kort over jouw expertisedomein. Graag ook categorie en/of periode en/of regio vermelden."></textarea-autosize>
      </div>
      <div class="required two fields" v-if="roles.vondstexpert">
        <div class="field" :class="{error:errors.function}">
          <label for="function">Functie</label>
          <input v-model="reg.function" type="text" id="function">
          <div v-for="msg in errors.function" v-text="msg" class="input"></div>
        </div>
        <div class="field" :class="{error:errors.affiliation}">
          <label for="affiliation">Instelling</label>
          <input v-model="reg.affiliation" type="text" id="affiliation">
          <div v-for="msg in errors.affiliation" v-text="msg" class="input"></div>
        </div>
      </div>
      <div class="field" v-show="roles.vondstexpert">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" v-model="roles.onderzoeker" value="true">
          <label>Ik ben wetenschappelijk onderzoeker en wil toegang krijgen tot exacte vondstlocatie.  Beschrijf in het veld 'onderzoek' hieronder kort waarvoor je die informatie wil gebruiken.
          Een MEDEA-medewerker zal je aanmelding evalueren, in samenspraak met het Agentschap Onroerend Erfgoed.</label>
        </div>
      </div>
      <div class="required field" v-if="roles.onderzoeker">
        <label for="research">Onderzoek</label>
        <textarea-autosize id="research" v-model="reg.research" placeholder="Schrijf iets kort over je onderzoeksproject."></textarea-autosize>
      </div>
      <div class="field" v-if="roles.detectorist || roles.vondstexpert || roles.registrator">
        <label for="bio">{{ biografieLabel }}</label>
        <textarea-autosize id="bio" v-model="reg.bio" :placeholder="biografiePlaceholder"></textarea-autosize>
      </div>
      <div class="field" v-if="roles.detectorist">
        <label for="email">Erkenningsnummer</label>
        <div class="ui message">
          Als je een erkend detectorist bent, vul dan hier je erkenningsnummer in. Een erkenning is verplicht voor detectorgebruikers sinds 1 april 2016. Deze informatie zal niet met derden gedeeld worden zonder je expliciete toestemming.
        </div>
        <div class="ui labeled input double-labeled">
          <div class="ui default label">
            OE/ERK/Metaaldetectorist/
          </div>
          <input type="text" v-model="oeerk.jjjj" placeholder="jjjj" style="min-width: 0;flex-shrink: 1">
          <div class="ui default label">
            /
          </div>
          <input type="text" v-model="oeerk.nnnnn" placeholder="00000" style="min-width: 0;flex-shrink: 1">
        </div>
      </div>
      <div v-show="roles.detectorist">
        <h3>Privacy</h3>
        <div class="field">
          <div class="ui checkbox">
            <input type="checkbox" tabindex="0" v-model="reg.showContactInfo">
            <label>Vermeld mijn naam op publiek toegankelijke vondstfiches</label>
          </div>
        </div>
        <br>
      </div>
      <p>
        Door te registreren verklaar je jezelf akkoord met de <a href="https://blog.vondsten.be/gebruikersvoorwaarden">gebruikersvoorwaarden en het privacy-beleid</a> van MEDEA.
      </p>
      <div class="field">
        <button type="submit" class="ui button" :class="submittable ? 'green' : ''" :disabled="!submittable">Registreer</button>
      </div>
      <div v-if="registered" style="color:#090">
        <h3>Registratie wordt gevalideerd</h3>
        <p>De administrator zal je registratie bevestigen. Dit kan even duren. In volgende iteraties van het platform kan dit automatisch gebeuren.</p>
      </div>
    </form>
  </div>
</div>
</template>

<script>

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

  import VueTextareaAutosize from "vue-textarea-autosize";
  import checkbox from 'semantic-ui-css/components/checkbox.min.js'

  import Ajax from '@/mixins/Ajax.js'
  import Notifications from '@/mixins/Notifications.js'

  export default {
    mounted () {
      console.log("mounted home");
      this.user = window.medeaUser;
      this.stats = window.stats;
    },
    watch: {
      roles (v) {
        console.log(v)
      }
    },
    data () {
      return {
        show: {
          password: false,
          roles: false
        },
        checked: false,
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
        user: null,
        stats: {}
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
        console.log(this.score);
        if (this.score < 2) {
          return false
        }

        if (this.roles.onderzoeker && !this.roles.vondstexpert) {
          this.roles.onderzoeker = false
        }

        if (!this.registered && this.reg.firstName && this.reg.lastName && this.reg.email && this.reg.password) {
          if (this.roles.vondstexpert && !this.reg.expertise) {
            console.log("no");
            return false
          }

          if (this.roles.onderzoeker && (!this.reg.research || !this.reg.affiliation || !this.reg.function)) {
            return false
          }
          console.log(this.roles);
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
    ready () {
      $('.ui.checkbox').checkbox()
      $('nav .ui.dropdown').dropdown()
    },
    mixins: [Ajax, Notifications],
    components: {
      VueTextareaAutosize
    }
  }
</script>
