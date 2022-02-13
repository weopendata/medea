<template>
  <div>
    <h4>Object</h4>
    <dl class="object-features_object-dl" v-if="find.object.objectNumberOfParts">
      <dt>Aantal</dt>
      <dd>{{find.object.objectNumberOfParts}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="find.object.objectMaterial">
      <dt>Materiaal</dt>
      <dd>{{find.object.objectMaterial}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="find.object.productionEvent&&find.object.productionEvent.productionTechnique&&find.object.productionEvent.productionTechnique.productionTechniqueType&&find.object.productionEvent.productionTechnique.productionTechniqueType.length">
      <dt>Techniek</dt>
      <dd>{{find.object.productionEvent.productionTechnique.productionTechniqueType}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="find.object.productionEvent&&find.object.productionEvent.productionTechnique&&find.object.productionEvent.productionTechnique.productionTechniqueSurfaceTreatmentType&&find.object.productionEvent.productionTechnique.productionTechniqueSurfaceTreatmentType.length">
      <dt>Oppervlaktebehandeling</dt>
      <dd>{{find.object.productionEvent.productionTechnique.productionTechniqueSurfaceTreatmentType}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="find.object.dimensions && find.object.dimensions.length">
      <template v-for="(dim, index) in find.object.dimensions">
        <dt>{{index == 0 ? 'Dimensies' : ''}}</dt>
        <dd>{{dim.dimensionType == 'diepte' ? 'dikte/hoogte' : dim.dimensionType}}:
          {{dim.measurementValue|comma}}{{dim.dimensionUnit}}</dd>
      </template>
    </dl>
    <dl class="object-features_object-dl" v-if="conservationTreatment">
      <dt>Conservatiebehandeling</dt>
      <dd>{{conservationTreatment}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="inscription">
      <dt>Opschrift</dt>
      <dd>{{inscription}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="mark">
      <dt>Merteken</dt>
      <dd>{{mark}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="typeDescription">
      <dt v-if="canUserSeeDetails">Typologische beschrijving</dt>
      <dd v-if="canUserSeeDetails">{{typeDescription}}</dd>
    </dl>
    <dl class="object-features_object-dl" v-if="find.object.objectDescription">
      <dt v-if="canUserSeeDetails">Beschrijving</dt>
      <dd v-if="canUserSeeDetails">{{find.object.objectDescription}}</dd>
    </dl>

    <h4>Typologie</h4>
    <dl>
      <dt>Categorie</dt>
      <dd>{{ typology.mainCategory }}</dd>
    </dl>
    <dl>
      <dt>Referentietype</dt>
      <dd><a :href="typologyUri(typology.code)" target="_blank">{{ typology.label + ' (' + typology.code + ')' }}</a></dd>
    </dl>
    <dl>
      <dt>Datering type</dt>
      <dd>{{ typologyDate }}</dd>
    </dl>
  </div>
</template>

<script>
  import {fromDate} from '../const.js'

  export default {
    props: ['find', 'typology'],
    computed: {
      inscription () {
        return this.findDistinguishingFeature('opschrift')
      },
      conservationTreatment () {
        return this.findDistinguishingFeature('conservering')
      },
      mark () {
        return this.findDistinguishingFeature('merkteken')
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
    methods: {
      findDistinguishingFeature(type) {
        if (!this.find || !this.find.object.distinguishingFeatures) {
          return
        }

        var conservationFeature = this.find.object.distinguishingFeatures.filter(feature => feature.distinguishingFeatureType === type)

        if (conservationFeature && conservationFeature.length > 0) {
          return conservationFeature[0].distinguishingFeatureNote
        }
      },
      typologyUri (code) {
        return window.location.protocol + "//" + window.location.host + '/typology-browser#' + code
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

<style lang="scss" scoped>
.object-features_object-dl {
  dd {
    margin-left: calc(160px + 1rem);
  }
}

  .object-features_location-dl {
    dd {
      margin-left: calc(170px + 1rem);
    }
  }
</style>
