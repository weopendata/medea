<template>
  <div :class="navClass">
    <nav class="ui container">
      <div class="ui secondary green pointing menu">
        <a href="/" class="item" :class="path == '/' ? 'active' : ''">Home</a>
        <a href="/finds" class="item" :class="path == 'finds' ? 'active' : ''">Vondsten</a>
        <a class="item" :class="path == '/collections' ? 'active' : ''" href="/collections" v-if="!isApplicationPublic">Collecties</a>
        <template v-if="! this.isGuest">
          <a href="/persons" class="item" :class="path == '/persons' ? 'active' : ''">Leden</a>
          <a href="/finds/create" class="item" :class="path == '/finds/create' ? 'active' : ''" data-step="2" data-intro="Klik hier om een nieuwe vondst te registreren." id="findsCreate">Nieuwe vondst</a>
        </template>
        <template v-if="isAdmin">
          <a href="/file-uploads" class="item" :class="path == '/file-uploads' ? 'active' : ''" data-step="3" data-intro="Klik hier om vondsten te importeren." id="uploads">Importeren</a>
        </template>
        <template v-if="isAdmin">
          <a href="/api-keys" class="item" :class="path == '/api-keys' ? 'active' : ''" data-step="4" data-intro="Klik hier om API keys te beheren." id="api-keys">API</a>
        </template>
        <template>
          <a href="/typology-browser" class="item" :class="path == '/typology-browser' ? 'active' : ''" data-step="5" data-intro="Klik hier om via de typologie vondsten te ontdekken" id="typology-browser">Typologie Browser</a>
        </template>
        <a class="item" target="_blank" :href="cmsLink">Over {{ cmsLabel }}</a>

        <div class="right menu">
          <template v-if="isGuest && !isApplicationPublic">
            <a href="/login" class="right floated item" :class=" path == '/login' ? 'active' : ''">Log in</a>
          </template>
          <template v-else-if="!isGuest">
            <a href="#" class="item" :class="path == '/help' ? 'active' : ''" onclick="startIntro();return false" v-if="!isApplicationPublic">Handleiding</a>
            <div class="ui top right pointing dropdown link item item-notif">
              <span class="text"><span class="ui red circular label" v-if="notifUnread" v-text="notifUnread" v-cloak></span> Meldingen</span>
              <i class="dropdown icon"></i>
              <div class="menu">
                <div v-if="notifications && notifications.length > 0" v-cloak>
                  <div class="item" v-for="(n, index) in notifications" v-text="n.message" :class="{read:n.read}" @click.stop="notifGo(n, index)"></div>
                </div>
                <div v-else class="item" @click.stop>Er zijn geen meldingen</div>
              </div>
            </div>

            <div class="ui top right pointing dropdown link item" id="the-dropdown">
              <span class="text">{{ this.user.name }}</span>
              <i class="dropdown icon"></i>
              <div class="menu">
                <div class="header">Profiel</div>
                <a :href="'/persons/' + user.id" @click.stop class="item">Profiel bekijken</a>
                <a href="/settings" @click.stop class="item">Profiel aanpassen</a>
                <div class="divider"></div>
                <a href="/logout" @click.stop class="item">Afmelden</a>
              </div>
            </div>
          </template>
        </div>
      </div>
    </nav>
  </div>
</template>

<script>
  import $ from 'jquery';
  import Notifications from '@/mixins/Notifications';

  import dropdown from 'semantic-ui-css/components/dropdown.min.js'
  import transition from 'semantic-ui-transition/transition.min.js'
  import GlobalSettings from "../mixins/GlobalSettings";

  export default {
    data () {
      return {
        user: {},
        cmsLink: '',
        cmsLabel: ''
      }
    },
    computed: {
      navClass () {
        var navClass = 'nav-top';

        if (this.path == '/') {
          navClass = "nav-home"
        }

        return navClass;
      },
      isGuest () {
        return ! this.user || ! this.user.email
      },
      isAdmin () {
        return this.user && this.user.administrator
      },
      path () {
        return window.location.pathname
      }
    },
    mounted() {
      this.user = window.medeaUser
      this.cmsLink = window.cmsLink
      this.cmsLabel = window.cmsLabel
    },
    created () {
      $(document).ready(function() {
        $('nav .ui.dropdown').dropdown()
      });
    },
    mixins: [Notifications, GlobalSettings]
  }
</script>
