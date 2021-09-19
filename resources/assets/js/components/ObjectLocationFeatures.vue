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

    <h4>Foto</h4>
    <dl v-for="photographFeature in photographFeatures">
      <dt>{{ photographFeature.key }}</dt>
      <dd>{{ photographFeature.value }}</dd>
    </dl>

    <h4>Toegankelijkheid</h4>
    <dl class="object-features_accessibility-dl" v-if="find.object.objectNr">
      <dt>Origineel inventarisnummer</dt>
      <dd>{{ find.object.objectNr }}</dd>
    </dl>
    <dl class="object-features_accessibility-dl" v-if="depot">
      <dt>Bewaarplaats van het ensemble</dt>
      <dd>{{ depot }}</dd>
    </dl>
    <dl class="object-features_accessibility-dl" v-if="archiveUri">
      <dt>Archiefreferentie</dt>
      <dd>{{ archiveUri }}</dd>
    </dl>
    <dl class="object-features_accessibility-dl" v-if="researchUri">
      <dt>Andere databanken</dt>
      <dd>{{ researchUri }}</dd>
    </dl>

    <h4>Deze vondstfiche</h4>
    <dl class="object-features_accessibility-dl">
      <dt>Referentie</dt>
      <dd>{{ currentLink }}</dd>
    </dl>

    <dl class="object-features_accessibility-dl">
      <dt>Citeer deze vondstfiche</dt>
      <dd class="cite">{{cite}}</dd>
    </dl>
  </div>
</template>

<script>
  import ContextTree from "./ContextTree";
  import FindHelper from "../mixins/FindHelper";

  export default {
    name: "ObjectLocationFeatures",
    props: ['context', 'excavation', 'find', 'typologyInformation'],
    computed: {
      photographFeatures () {
        var photographFeatures = []

        if (this.find.object && this.find.object.photograph && this.find.object.photograph.length > 0) {
          var photograph = this.find.object.photograph[0]

          photographFeatures.push({
            key: 'Licentie',
            value: photograph.photographRights && photograph.photographRights.photographRightsLicense
          })

          photographFeatures.push({
            key: 'Attributie',
            value: photograph.photographRights && photograph.photographRights.photographRightsAttribution
          })

          photographFeatures.push({
            key: 'Caption',
            value: photograph.photographCaption
          })

          photographFeatures.push({
            key: 'Opmerking',
            value: photograph.photographNote
          })
        }

        return photographFeatures.filter(r => r.value)
      },
      finder() {
        return window.publicUserInfo || {}
      },
      cite() {
        const d = new Date()
        d.setHours(12)

        return (this.finder.name || 'Middeleeuws Metaal')
          + ' (' + this.find.created_at.slice(0, 4) + '). ' +
          this.findTitle + ', MEDEA-ID ' + this.find.identifier +
          '. Geraadpleegd op ' + d.toJSON().slice(0, 10) +
          ' via ' + window.location.href
      },
      depot () {
        if (!this.excavation || !this.excavation.collection || !this.excavation.collection.group) {
          return
        }

        var depot = this.excavation.collection.group.institutionName

        if (this.excavation.collection.group.institutionAddress) {
          depot += ', ' + this.excavation.collection.group.institutionAddress
        }

        return depot
      },
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

        if (! this.excavation.publication[0].publicationArchiveURI) {
          return
        }

        return this.excavation.publication[0].publicationArchiveURI
      },
      researchUri () {
        if (!this.excavation.publication) {
          return
        }

        if (this.excavation.publication.length === 0) {
          return
        }

        if (! this.excavation.publication[0].publicationResearchURI) {
          return
        }

        return this.excavation.publication[0].publicationResearchURI
      },
    },
    components: {
      ContextTree
    },
    mixins: [FindHelper]
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

  .object-features_accessibility-dl {
    dd {
      margin-left: calc(175px + 1rem);
    }
  }
</style>
