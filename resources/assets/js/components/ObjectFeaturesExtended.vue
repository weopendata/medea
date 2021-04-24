<template>
  <div>
    <h4>Object</h4>
    <dl v-if="find.object.dimensions && find.object.dimensions.length">
      <dt>Dimensies</dt>
      <dd v-for="dim in find.object.dimensions">{{dim.dimensionType == 'diepte' ? 'dikte/hoogte' : dim.dimensionType}}:
        {{dim.measurementValue|comma}}{{dim.dimensionUnit}}
      </dd>
    </dl>
    <dl v-if="find.object.objectMaterial">
      <dt>Materiaal</dt>
      <dd>{{find.object.objectMaterial}}</dd>
    </dl>
    <dl v-if="find.object.amount">
      <dt>Aantal</dt>
      <dd>{{find.object.amount}}</dd>
    </dl>
    <dl v-if="find.object.productionEvent&&find.object.productionEvent.productionTechnique&&find.object.productionEvent.productionTechnique.productionTechniqueType&&find.object.productionEvent.productionTechnique.productionTechniqueType.length">
      <dt>Techniek</dt>
      <dd>{{find.object.productionEvent.productionTechnique.productionTechniqueType}}</dd>
    </dl>
    <dl v-if="find.object.treatmentEvent&&find.object.treatmentEvent.modificationTechnique&&find.object.treatmentEvent.modificationTechnique.modificationTechniqueType">
      <dt>Behandeling</dt>
      <dd>{{find.object.treatmentEvent.modificationTechnique.modificationTechniqueType}}</dd>
    </dl>
    <dl v-if="find.object.objectInscription&&find.object.objectInscription.objectInscriptionNote">
      <dt>Opschrift</dt>
      <dd>{{find.object.objectInscription.objectInscriptionNote}}</dd>
    </dl>
    <dl v-if="find.object.objectDescription">
      <dt v-if="canUserSeeDetails">Beschrijving</dt>
      <dd v-if="canUserSeeDetails">{{find.object.objectDescription}}</dd>
    </dl>
    <dl v-if="typeDescription">
      <dt v-if="canUserSeeDetails">Type-beschrijving</dt>
      <dd v-if="canUserSeeDetails">{{typeDescription}}</dd>
    </dl>

    <h4>Typologie</h4>
    <dl>
      <dt>Category</dt>
      <dd>{{ typology.mainCategory }}</dd>
    </dl>
    <dl>
      <dt>Referentietype</dt>
      <dd><a :href="typology.uri" target="_blank">{{ typology.label + ' (' + typology.code + ')' }}</a></dd>
    </dl>
    <dl>
      <dt>Datering type</dt>
      <dd>{{ typologyDate }}</dd>
    </dl>

    <h4>Vindplaats</h4>
    <dl>
      <dt>Locatie & tijdstip opgraving</dt>
      <dd>{{ excavationContext }}</dd>
    </dl>
    <dl v-if="contextDating">
      <dt>Datering context</dt>
      <dd>{{ contextDating }}</dd>
    </dl>
    <dl v-if="contextInterpretation">
      <dt>Interpretatie context</dt>
      <dd>{{ contextInterpretation }}</dd>
    </dl>

    <h4>Toegankelijkheid</h4>
    <dl>
      <dt>Referentie</dt>
      <dd>{{ currentLink }}</dd>
    </dl>
    <dl v-if="archiveUri">
      <dt>Archiefreferentie</dt>
      <dd>{{ archiveUri }}</dd>
    </dl>
    <dl v-if="researchUri">
      <dt>Onderzoeksreferentie</dt>
      <dd>{{ researchUri }}</dd>
    </dl>


  </div>
</template>

<script>
  import {fromDate} from '../const.js'

  export default {
    props: ['find', 'typology', 'excavation', 'context'],
    computed: {
      archiveUri () {
        if (this.excavation.publication && this.excavation.publication.length === 0) {
          return
        }

        return this.excavation.publication[0].archiveURI
      },
      researchUri () {
        if (this.excavation.publication && this.excavation.publication.length === 0) {
          return
        }

        return this.excavation.publication[0].researchURI
      },
      currentLink () {
        return window.location.href
      },
      contextInterpretation () {
        return this.context.contextInterpretation
      },
      contextDating() {
        if (!this.context) {
          return
        }

        if (!this.context.contextDating || !this.context.contextDating.contextDatingPeriod) {
          return
        }

        var dating = this.context.contextDating.contextDatingPeriod

        if (this.context.contextDating.contextDatingTechnique && this.context.contextDating.contextDatingTechnique.contextDatingPeriodMethod) {
          dating += ', ' + this.context.contextDating.contextDatingTechnique.contextDatingPeriodMethod
        }

        return dating
      },
      excavationContext() {
        var context = ''

        if (!this.excavation.searchArea) {
          return context
        }

        if (this.excavation.searchArea.location && this.excavation.searchArea.location.locationPlaceName) {
          context = this.excavation.searchArea.location.locationPlaceName.appellation
        }

        if (this.excavation.searchArea.location && this.excavation.searchArea.location.address && this.excavation.searchArea.location.address.locationAddressLocality) {
          context += ' ' + this.excavation.searchArea.location.address.locationAddressLocality
        }

        context += ', ' + this.excavation.excavationPeriod

        return context
      },
      typologyDate() {
        var initialPeriod = 'onbekend';
        var finalPeriod = 'onbekend'

        if (this.typology.initialPeriod && this.typology.initialPeriod.label) {
          initialPeriod = this.typology.initialPeriod.startyear
        }

        if (this.typology.finalPeriod && this.typology.finalPeriod.label) {
          finalPeriod = this.typology.finalPeriod.endyear
        }

        return initialPeriod + ' - ' + finalPeriod
      },
      typeDescription() {
        return this.find.object.productionEvent
          && this.find.object.productionEvent.productionClassification
          && this.find.object.productionEvent.productionClassification.length > 0
          && this.find.object.productionEvent.productionClassification[0].productionClassificationDescription
      },
      humanizedCoordinates() {
        if (this.find.findSpot && this.find.findSpot.location && this.find.findSpot.location.lat) {
          return this.find.findSpot.location.lng + ' O , ' + this.find.findSpot.location.lat + ' N (WGS84)'
        }
      },
      canUserSeeDetails() {
        // The coordinates returned from the API are already transformed according to the role of the user
        if (!this.user || this.user.isGuest || !this.user.id) {
          return false
        }

        return this.user.administrator || this.user.onderzoeker || this.user.id == this.finder.id
      },
      user() {
        return window.medeaUser
      }
    },
    filters: {
      comma(n) {
        return n.toString().replace('.', ',')
      },
      fromDate
    }
  }
</script>

<style scoped>

</style>
