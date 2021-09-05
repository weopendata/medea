<template>
  <div>
    <h4>Vindplaats</h4>
    <dl class="object-features_location-dl">
      <dt>Opgraving</dt>
      <dd>{{ excavationContext }}</dd>
    </dl>

    <dl class="object-features_location-dl" v-if="excavationSifting">
      <dt>Zeven</dt>
      <dd>{{ excavationSifting }}</dd>
    </dl>
    <dl class="object-features_location-dl" v-if="excavationMetalDetection">
      <dt>Metaaldetectie</dt>
      <dd>{{ excavationMetalDetection }}</dd>
    </dl>

    <context-tree :is-root="true" :excavation="excavation" :context="find.object.context"></context-tree>

    <h4>Toegankelijkheid</h4>
    <dl class="object-features_location-dl" v-if="find.object.objectNr">
      <dt>Origineel inventarisnummer</dt>
      <dd>{{ find.object.objectNr }}</dd>
    </dl>
    <dl class="object-features_location-dl">
      <dt>Referentie</dt>
      <dd>{{ currentLink }}</dd>
    </dl>
    <dl class="object-features_location-dl" v-if="archiveUri">
      <dt>Archiefreferentie</dt>
      <dd>{{ archiveUri }}</dd>
    </dl>
    <dl class="object-features_location-dl" v-if="researchUri">
      <dt>Onderzoeksreferentie</dt>
      <dd>{{ researchUri }}</dd>
    </dl>
  </div>
</template>

<script>
  import ContextTree from "./ContextTree";

  export default {
    name: "ObjectLocationFeatures",
    props: ['context', 'excavation', 'find'],
    computed: {
      currentLink () {
        return window.location.href
      },
      excavationSifting () {
        return this.excavation && this.excavation.excavationProcedureSifting && this.excavation.excavationProcedureSifting.excavationProcedureSiftingType
      },
      excavationMetalDetection () {
        return this.excavation && this.excavation.excavationProcedureMetalDetection && this.excavation.excavationProcedureMetalDetection.excavationProcedureMetalDetectionType
      },
      excavationContext() {
        var context = ''

        if (!this.excavation.searchArea) {
          return context
        }

        /*if (this.excavation.searchArea.location && this.excavation.searchArea.location.locationPlaceName) {
          context = this.excavation.searchArea.location.locationPlaceName.appellation
        }

        if (this.excavation.searchArea.location && this.excavation.searchArea.location.address && this.excavation.searchArea.location.address.locationAddressLocality) {
          context += ' ' + this.excavation.searchArea.location.address.locationAddressLocality
        }*/


        return this.excavation.excavationTitle + ', ' + this.excavation.excavationPeriod
      },
      archiveUri () {
        if (!this.excavation.publication) {
          return
        }

        if (this.excavation.publication.length === 0) {
          return
        }

        if (! this.excavation.publication[0].archiveURI) {
          return
        }

        return this.excavation.publication[0].archiveURI
      },
      researchUri () {
        if (!this.excavation.publication) {
          return
        }

        if (this.excavation.publication.length === 0) {
          return
        }

        if (! this.excavation.publication[0].researchURI) {
          return
        }

        return this.excavation.publication[0].researchURI
      },
    },
    components: {
      ContextTree
    }
  }
</script>

<style lang="scss" scoped>
  .object-features_object-dl {
    dd {
      margin-left: calc(150px + 1rem);
    }
  }

  .object-features_location-dl {
    dd {
      margin-left: calc(170px + 1rem);
    }
  }
</style>
