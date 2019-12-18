<template>
  <form @submit.prevent="updateSettings" class="ui container form" novalidate>
    <div class="nav-settings">
      <h3 class="facet-title">Instellingen</h3>
      <ul>
        <li><a href="#contact">Contactgegevens</a></li>
        <li><a href="#profiel">Profiel</a></li>
        <li><a href="#privacy">Privacy</a></li>
        <li><a href="#rollen">Rollen</a></li>
      </ul>
      <p>
        <button class="ui green button">Instellingen bewaren</button>
      </p>
      <p v-if="message" v-cloak>
        {{ message }}
      </p>
    </div>
    <div class="container-settings">

      <h2 id="contact">Contactgegevens</h2>
      <div class="two fields">
        <div class="field" :class="{error:errors.firstName}">
          <label for="firstName">Voornaam</label>
          <input v-model="user.firstName" type="text" id="firstName">
          <div v-for="msg in errors.firstName" v-text="msg" class="input"></div>
        </div>
        <div class="field" :class="{error:errors.lastName}">
          <label for="lastName">Achternaam</label>
          <input v-model="user.lastName" type="text" id="lastName">
          <div v-for="msg in errors.lastName" v-text="msg" class="input"></div>
        </div>
      </div>
      <div class="two fields">
        <div class="field">
          <label for="email">Email</label>
          <input v-model="email" type="email" disabled>
        </div>
        <div class="field" :class="{error:errors.phone}">
          <label for="last_name">Telefoon</label>
          <input v-model="user.phone" type="text">
        </div>
      </div>

      <p class="cta-profile">
        <a :href="'/persons/'+id" class="ui blue button">Profiel bekijken</a>
      </p>
      <h2 id="profiel">Profiel</h2>
      <div class="two fields">
        <div class="field" :class="{error:errors.firstName}">
          <label for="function">Functie</label>
          <input v-model="user.function" type="text" id="function">
        </div>
        <div class="field" :class="{error:errors.affiliation}">
          <label for="affiliation">Instelling</label>
          <input v-model="user.affiliation" type="text" id="affiliation">
        </div>
      </div>
      <div class="field" v-if="roles.detectorist" :class="{error:errors.detectorist}">
        <label for="email">Erkenningsnummer</label>
        <div class="ui labeled input double-labeled">
          <div class="ui default label">
            OE/ERK/Metaaldetectorist/
          </div>
          <input type="text" v-model="jjjj" placeholder="jjjj" style="min-width: 0;flex-shrink: 1">
          <div class="ui default label">
            /
          </div>
          <input type="text" v-model="nnnnn" placeholder="00000" style="min-width: 0;flex-shrink: 1">
        </div>
      </div>
      <div class="field" v-if="roles.vondstexpert">
        <label for="role">Expertise</label>
        <textarea id="description" name="description" rows="5" placeholder="Schrijf iets kort over jouw expertisedomein. Graag ook categorie en/of periode en/of regio vermelden." v-model="user.expertise"></textarea>
      </div>
      <div class="field" v-if="roles.onderzoeker">
        <label for="role">Onderzoek</label>
        <textarea id="description" name="description" rows="5" placeholder="Schrijf iets kort over je onderzoeksproject." v-model="user.research"></textarea>
      </div>
      <div class="field" v-if="roles.detectorist || roles.vondstexpert || roles.registrator">
        <label for="role">{{ biografieLabel }}</label>
        <textarea id="description" name="description" rows="5" :placeholder="biografiePlaceholder" v-model="user.bio"></textarea>
      </div>
      <div class="field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" v-model="user.showEmail">
          <label>
            <b>Toon mijn emailadres op mijn profiel.</b>
          </label>
        </div>
      </div>

      <h2 id="privacy">Privacy</h2>
      <div class="field">
        <label>Profiel openstellen voor</label>
        <select class="ui dropdown" v-model="user.profileAccessLevel">
          <option v-for="(accessLevel, index) in accessLevels" :value="index">{{ accessLevel }}</option>
        </select>
      </div>
      <div class="field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" v-model="user.showNameOnPublicFinds">
          <label>
            <b>Vermeld mijn naam op publiek toegankelijke vondstfiches</b>
          </label>
        </div>
      </div>
      <div class="field">
      <div class="last-page">
        <h2 id="rollen">Rollen</h2>
        <p>
          Alleen de administrator kan de rollen wijzigen.
        </p>
        <div class="field">
          <div class="ui checkbox disabled">
            <input type="checkbox" tabindex="0" v-model="roles.detectorist">
            <label>
              <b>Detectorist</b>
              <br>Je kan je eigen vonsten documenteren en publiceren.
            </label>
          </div>
        </div>
        <div class="field">
          <div class="ui checkbox disabled">
            <input type="checkbox" tabindex="0" v-model="roles.registrator">
            <label>
              <b>Registrator</b>
              <br>Je kan vondsten van anderen documenteren en publiceren in het kader van een bestaande collectie of project.
            </label>
          </div>
        </div>
        <div class="field">
          <div class="ui checkbox disabled">
            <input type="checkbox" tabindex="0" v-model="roles.validator">
            <label>
              <b>Validator</b>
              <br>Je gaat na of gemelde vondsten in orde zijn voor publicatie.
            </label>
          </div>
        </div>
        <div class="field">
          <div class="ui checkbox disabled">
            <input type="checkbox" tabindex="0" v-model="roles.vondstexpert">
            <label>
              <b>Vondstexpert</b>
              <br>Je hebt expertise op basis van praktijk- en/of onderzoekservaring die je wil inzetten om vondsten te bestuderen en classificeren.
            </label>
          </div>
        </div>
        <div class="field" v-model="roles.vondstexpert">
          <div class="ui checkbox disabled">
            <input type="checkbox" tabindex="0" v-model="roles.onderzoeker">
            <label>
              <b>Onderzoeker</b>
              <br>Je hebt toegang tot exacte vondstlocaties
            </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>
</template>

<script>
  import checkbox from 'semantic-ui-css/components/checkbox.min.js'

  import Notifications from '@/mixins/Notifications'

  /*var email = window.user.email
  var id = window.user.id
  delete window.user.email*/

  export default {
    data () {
      return {
        id: window.medeaUser.id,
        email: window.medeaUser.email,
        medeaUser: window.medeaUser,
        message: null,
        roles: window.medeaUser,
        user: window.user,
        accessLevels: window.accessLevels,
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
      updateSettings () {
        this.user.verified = undefined
        this.user.password = undefined
        this.user.email = undefined
        this.user.personType = undefined
        this.user.savedSearches = undefined

        axios.put('/persons/' + id, this.user)
        .then(response => {
          this.message = response.data.message
          this.errors = {}
          this.submitting = false;
        })
        .catch(err => {
          console.log(err, "ERROR");
          this.message = response.data.message
          this.errors = response.data
          this.submitting = false;
        });
      }
    },
    mounted () {
      $('.ui.checkbox', this.$el).checkbox()
    },
    mixins: [Notifications]
  }
</script>

<style type="text/css">
.nav-settings ul {
  list-style: none;
  margin: 0;
  padding: 0;
}
.nav-settings a {
  display: block;
  line-height: 2em;
}
.nav-settings p {
  margin: 1em 0;
}
.nav-top>nav {
  background: #f0f4f8;
}
.last-page {
  min-height: 100vh
}
.cta-profile {
  margin-top: 2rem;
}
@media (max-width: 479px) {
  .facet-title {
    display: none;
  }
}
@media (min-width: 480px) {
  .container-settings {
    margin-left: 14em;
    max-width: 30em;
  }
  .container-settings h2 {
    margin-top: 0;
    padding-top: 60px;
  }
  .nav-top {
    position: fixed;
    z-index: 2;
    width: 100%;
  }
  .nav-settings {
    position: fixed;
    top: 70px;
  }
  .cta-profile {
    float:right;
    margin-top:60px;
    margin-bottom:-20px;
  }
}
@media (min-width: 769px) {
  .container-settings {
    margin-left: 14em;
    max-width: 30em;
  }
  .nav-top {
    position: fixed;
    z-index: 2;
    width: 100%;
  }
  .nav-settings {
    position: fixed;
    top: 70px;
  }
}
</style>