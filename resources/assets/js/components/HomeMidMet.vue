<template>
  <div>
    <div class="midmet-hero">
      <form method="GET" action="/finds" class="ui container">
        <p class="lead" data-step="1" data-intro="Deze intro zal je begeleiden bij het registreren van een vondst.">
          Welkom op Middeleeuws Metaal. Deze databank ontsluit typologische en contextuele gegevens over <span
            v-if="stats.validatedFinds">{{stats.validatedFinds}}</span> middeleeuwse metaalvondsten uit
          <span v-if="stats.excavations">{{stats.excavations}}</span> archeologische opgravingen in Vlaamse
          steden.
        </p>
        <p>
          Doorzoek de databank via de <a href="/finds">Vondstlijst</a>, de <a
            href="/typology-browser">Typologie-browser</a> of het zoekvenster hieronder.
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
          <button type="submit" class="ui green button"><i class="sign in icon"></i> Vondsten doorzoeken</button>
        </p>
        <div style="max-width: 600px;">
          <p>
            Middeleeuws Metaal is een synthese-onderzoek (2020-2022) gefinancierd door het <a target="_blank" href="https://www.onroerenderfgoed.be/">Agentschap Onroerend Erfgoed</a> en
            uitgevoerd door <a href="https://vub.be/" target="_blank">Vrije Universiteit Brussel</a> (coördinerende partner)
            in samenwerking met <a href="https://www.artefactresearch.be/" target="_blank">Center for Artefact Research</a>, <a target="_blank" href="https://www.co7.be/">CO7</a>, <a href="https://stad.gent/nl/cultuur-sport-vrije-tijd/cultuur/dienst-stadsarcheologie-en-monumentenzorg-team-stadsarcheologie">Stad Gent - Dienst Stadsarcheologie en Monumentenzorg</a>,
            <a href="https://www.antwerpenmorgen.be/nl/toekomstvisies/visie-archeologie/over" target="_blank">Stedelijke Archeologische Dienst Stad Antwerpen</a>,
            <a href="https://historiesvzw.be/" target="_blank">Histories</a>, <a href="https://portable-antiquities.nl/pan/#/public" target="_blank"> Portable Antiquities Nederland</a>, <a href="https://meemoo.be/en">meemoo</a> en <a href="https://weconnectdata.com/" target="_blank">We
            Connect Data</a>.
          </p>
          <p><a :href="cmsLink" target="_blank">Meer informatie over dit project.</a></p>
        </div>
        <div style="margin-top: 2.5rem;">
          <img src="/images/midmet-logos.png"/>
        </div>
      </form>
    </div>
  </div>
  </div>
</template>

<script>

import GlobalSettings from '../mixins/GlobalSettings';
import VueTextareaAutosize from 'vue-textarea-autosize';

import Ajax from '@/mixins/Ajax.js'
import Notifications from '@/mixins/Notifications.js'

export default {
  data() {
    return {
      user: null,
      cmsLink: '',
    }
  },
  mounted () {
    this.user = window.medeaUser;
    this.cmsLink = window.cmsLink;
  },
  computed: {
    stats () {
      return window.stats;
    }
  },
  mixins: [Ajax, Notifications, GlobalSettings],
  components: {
    VueTextareaAutosize
  }
}
</script>

<style lang="scss" scoped>
.midmet-hero {
  display: flex;
  align-items: center;
  padding: 10% 0;
  min-height: 100vh;
  -webkit-background-size: cover;
  -moz-background-size: cover;
  -o-background-size: cover;
  background-size: cover;
  .lead {
    font-size: 1.6em;
    max-width: 33em;
  }
  @media only screen and (min-width: 1170px) {
    .hero {
      font-size: 16px;
    }
    .lead {
      font-size: 1.8em;
      max-width: 28em;
    }
  }
  .ui.button {
  }
}
</style>
