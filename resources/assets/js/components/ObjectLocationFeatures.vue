<template>
  <div style="margin-top: 1rem;">
    <h4>Vindplaats</h4>
    <dl class="object-features_location-dl">
      <dt>Opgraving</dt>
      <dd>{{excavationContext}}</dd>
    </dl>

    <!--<dl class="object-features_location-dl" v-if="excavationSifting">
      <dt>Zeven</dt>
      <dd>{{ excavationSifting }}</dd>
    </dl>-->

    <context-tree :is-root="true" :excavation="excavation" :context="find.object.context" v-if="find.object.context"/>

    <dl class="object-features_location-dl" v-if="excavationMetalDetection">
      <dt>Metaaldetectie</dt>
      <dd>{{excavationMetalDetection}}</dd>
    </dl>

    <h4>Inventarisatie</h4>
    <dl v-for="inventoryFeature in inventoryFeatures" class="object-features_inventory-dl">
      <dt>{{inventoryFeature.key}}</dt>
      <dd>{{inventoryFeature.value}}</dd>
    </dl>

    <h4>Foto</h4>
    <dl v-for="photographFeature in photographFeatures">
      <dt>{{photographFeature.key}}</dt>
      <dd>{{photographFeature.value}}</dd>
    </dl>

    <h4>Toegankelijkheid</h4>
    <dl class="object-features_accessibility-dl" v-if="find.object.objectNr">
      <dt>Origineel inventarisnummer</dt>
      <dd>{{find.object.objectNr}}</dd>
    </dl>

    <dl class="object-features_accessibility-dl" v-if="legacyId">
      <dt>Inventarisnummer vondst</dt>
      <dd>{{legacyId}}</dd>
    </dl>

    <dl class="object-features_accessibility-dl" v-if="depot">
      <dt>Bewaarplaats van het ensemble</dt>
      <dd>{{depot}}</dd>
    </dl>

    <dl class="object-features_accessibility-dl" v-if="reportsAndPublications.length">
      <dt>Rapporten en publicaties</dt>
      <dd v-for="reportOrPublication in reportsAndPublications">
        <a v-if="reportOrPublication.type === 'link'" target="_blank" :href="reportOrPublication.value">{{ reportOrPublication.value }}</a>
        <span v-else>{{ reportOrPublication.value }}</span>
      </dd>
    </dl>

    <!--<dl class="object-features_accessibility-dl" v-if="archiveUri">
      <dt>Archiefreferentie</dt>
      <dd><a :href="archiveUri" target="_blank">{{archiveUri}}</a></dd>
    </dl>

    <dl class="object-features_accessibility-dl" v-if="researchUri">
      <dt>Andere databanken</dt>
      <dd><a :href="researchUri" target="_blank"> {{researchUri}}</a></dd>
    </dl>-->

    <h4>Deze vondstfiche</h4>
    <dl class="object-features_accessibility-dl">
      <dt>Referentie</dt>
      <dd>{{currentLink}}</dd>

      <dt>MEDEA ID</dt>
      <dd>{{ find.identifier }}</dd>

      <template v-if="find.excavationId">
        <dt>Excavation ID</dt>
        <dd>{{find.excavationId}}</dd>
      </template>

      <template v-if="find.internalId">
        <dt>Vondst ID</dt>
        <dd>{{find.internalId}}</dd>
      </template>
    </dl>

    <dl class="object-features_accessibility-dl">
      <dt>Citeer deze vondstfiche</dt>
      <dd class="cite">{{cite}}</dd>
    </dl>
  </div>
</template>

<script>
import ContextTree from './ContextTree';
import FindHelper from '../mixins/FindHelper';

export default {
  name: 'ObjectLocationFeatures',
  props: ['context', 'excavation', 'find', 'typologyInformation'],
  computed: {
    reportsAndPublications () {
      let reportsAndPublications = []

      reportsAndPublications.push({ value: this.excavationReportAuthorString, type: 'text' })
      reportsAndPublications.push({ value: this.archiveUri, type: 'link' })
      reportsAndPublications.push({ value: this.researchUri, type: 'link' })

      return reportsAndPublications.filter(r => r.value)
    },
    excavationReportAuthorString () {
      if (!this.excavation?.publication || !this.excavation.publication.length) {
        return
      }

      const publication = this.excavation.publication[0]

      if (!publication?.publicationContact) {
        return
      }

      let reportAuthorString = publication.publicationContact

      if (!publication.publicationCreation[0]) {
        return reportAuthorString
      }

      const publicationCreation = publication.publicationCreation[0]

      if (publicationCreation?.publicationCreationTimeSpan?.date) {
        reportAuthorString += ', ' + publicationCreation.publicationCreationTimeSpan.date + '.'
      }

      if (publication.publicationTitle) {
        reportAuthorString += ' ' + publication.publicationTitle + ', '
      }

      if (publicationCreation?.publicationCreationActor?.publicationCreationActorName) {
        reportAuthorString += publicationCreation.publicationCreationActor.publicationCreationActorName
      }

      if (publicationCreation?.publicationCreationLocation?.publicationCreationLocationAppellation) {
        reportAuthorString += ' ' + publicationCreation.publicationCreationLocation.publicationCreationLocationAppellation
      }

      return reportAuthorString
    },
    inventoryFeatures () {
      let inventoryFeatures = []

      const inventoryFeatureMapping = [
        {
          excavationProperty: 'inventoryCompleteness',
          featureKey: 'Volledigheid inventarisatie'
        },
        {
          excavationProperty: 'remarks',
          featureKey: 'Opmerkingen'
        },
      ]

      inventoryFeatureMapping.forEach((mapping) => {
        inventoryFeatures.push({
          key: mapping.featureKey,
          value: this.excavation[mapping.excavationProperty]
        })
      })

      return inventoryFeatures.filter(f => f.value)
    },
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
    legacyId () {
      return this.context?.contextLegacyId?.contextLegacyIdValue instanceof String ? this.context.contextLegacyId.contextLegacyIdValue : null
    },
    finder () {
      return window.publicUserInfo || {}
    },
    cite () {
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
    excavationContext () {
      var context = ''

      if (!this.excavation.searchArea) {
        return context
      }

      return this.excavation.excavationTitle + ', ' + this.excavation.excavationPeriod
    },
    archiveUri () {
      if (!this.excavation.publication) {
        return
      }

      if (this.excavation.publication.length === 0) {
        return
      }

      if (!this.excavation.publication[0].publicationArchiveURI) {
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

      if (!this.excavation.publication[0].publicationResearchURI) {
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

.object-features_inventory-dl {
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
