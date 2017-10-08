<template>
  <h4>Vondst</h4>
  <dl>
    <dt-check v-if="validating" prop="findDate" data-tooltip="Duid aan wat gewijzigd moet worden" data-position="top left" data-green="" id="dt-check-help"></dt-check>
    <dt>Datum</dt>
    <dd>{{find.findDate | fromDate}}</dd>
  </dl>
  <dl v-if="find.findSpot">
    <dt-check v-if="validating" prop="location"></dt-check>
    <dt>Locatie</dt>
    <dd>{{find.findSpot.location.address && find.findSpot.location.address.locationAddressLocality}}&nbsp;</dd>
    <dt v-if="canUserSeeDetails">Coordinaten</dt>
    <dd v-if="canUserSeeDetails">{{humanizedCoordinates}}</dd>
    <dt v-if="validating || canUserSeeDetails">Coordinaatprecisie</dt>
    <dd v-if="validating || canUserSeeDetails">{{humanizedAccuracy}}&nbsp;</dd>
    <dt v-if="find.findSpot.findSpotType">Type vindplaats</dt>
    <dd>{{find.findSpot.findSpotType}}</dd>
    <dt v-if="find.findSpot.findSpotTitle">Lokale plaatsnaam</dt>
    <dd>{{find.findSpot.findSpotTitle}}</dd>
    </div>
  </dl>
  <dl v-if="finder&&finder.name">
    <br/>
    <dt-check v-if="validating" prop="finder.name"></dt-check>
    <dt>Vinder</dt>
    <dd>
      <a v-if="finder.id" :href="'/persons/'+finder.id" v-text="finder.name"></a>
      <span v-else v-text="finder.name"></span>
      &nbsp;
    </dd>
  </dl>
  <dl v-if="user.administrator && find.object.validated_by">
    <dt>Gepubliceerd door</dt>
    <dd>{{find.object.validated_by}}</dd>
  </dl>
  <h4>Object</h4>
  <dl v-if="find.object.objectDescription">
    <dt v-if="canUserSeeDetails">Beschrijving</dt>
    <dd v-if="canUserSeeDetails">{{find.object.objectDescription}}</dd>
  </dl>
  <dl v-if="find.object.objectInscription&&find.object.objectInscription.objectInscriptionNote">
    <dt-check v-if="validating" prop="objectInscriptionNote"></dt-check>
    <dt>Opschrift</dt>
    <dd>{{find.object.objectInscription.objectInscriptionNote}}</dd>
  </dl>
  <dl v-if="find.object.dimensions && find.object.dimensions.length">
    <dt-check v-if="validating" prop="dimensions"></dt-check>
    <dt>Dimensies</dt>
    <dd v-for="dim in find.object.dimensions">{{dim.dimensionType == 'diepte' ? 'hoogte/dikte' : dim.dimensionType}}: {{dim.measurementValue|comma}}{{dim.dimensionUnit}}</dd>
  </dl>
  <dl v-if="find.object.objectMaterial">
  <dt-check v-if="validating" prop="objectMaterial"></dt-check>
  <dt>Materiaal</dt>
  <dd>{{find.object.objectMaterial}}</dd>
</dl>
  <dl v-if="find.object.productionEvent&&find.object.productionEvent.productionTechnique&&find.object.productionEvent.productionTechnique.productionTechniqueType&&find.object.productionEvent.productionTechnique.productionTechniqueType.length">
    <dt-check v-if="validating" prop="productionTechniqueType"></dt-check>
    <dt>Techniek</dt>
    <dd>{{find.object.productionEvent.productionTechnique.productionTechniqueType}}</dd>
  </dl>
  <dl v-if="find.object.treatmentEvent&&find.object.treatmentEvent.modificationTechnique&&find.object.treatmentEvent.modificationTechnique.modificationTechniqueType">
    <dt-check v-if="validating" prop="modificationTechniqueType"></dt-check>
    <dt>Behandeling</dt>
    <dd>{{find.object.treatmentEvent.modificationTechnique.modificationTechniqueType}}</dd>
  </dl>
  <dl v-if="find.object.objectCategory">
    <dt-check v-if="validating" prop="objectCategory"></dt-check>
    <dt>Categorie</dt>
    <dd>{{find.object.objectCategory}}</dd>
  </dl>
  <dl v-if="find.object.period">
    <dt-check v-if="validating" prop="period"></dt-check>
    <dt>Periode</dt>
    <dd>{{periodOverruled || find.object.period}}</dd>
  </dl>
  <h4>Details</h4>
  <dl v-if="find.updated_at!==find.created_at">
    <dt>Gewijzigd op</dt>
    <dd>{{find.updated_at | fromDate}}</dd>
  </dl>
  <dl>
    <dt>Toegevoegd</dt>
    <dd>{{find.created_at | fromDate}}</dd>
  </dl>
  <dl v-if="find.object.validated_at">
    <dt>Gevalideerd op</dt>
    <dd>{{find.object.validated_at | fromDate}}</dd>
  </dl>
  <dl>
    <dt>Status</dt>
    <dd>{{find.object.objectValidationStatus}}</dd>
  </dl>
  <template v-if="find.object.objectNr || (find.object.collection && find.object.collection.title)">
    <h4>Collectie</h4>
    <dl>
      <dt>Titel</dt>
      <dd><a v-if="find.object.collection && find.object.collection.title" :href="'/collections/' + find.object.collection.identifier">{{ find.object.collection.title }}</a><span v-else>Niet gespecificeerd</span></dd>
    </dl>
    <dl v-if="find.object.objectNr">
      <dt>Inventarisnummer</dt>
      <dd>{{find.object.objectNr}}</dd>
    </dl>
  </template>
</template>

<script>
import DtCheck from './DtCheck.vue'
import {fromDate} from '../const.js'

function sameValues(array) {
  return !!array.reduce((a, b) => a === b ? a : NaN )
}

export default {
  props: ['find', 'feedback', 'validating'],
  computed: {
    humanizedCoordinates () {
      if (this.find.findSpot && this.find.findSpot.location && this.find.findSpot.location.lat) {
        return this.find.findSpot.location.lat + ', ' + this.find.findSpot.location.lng
      }
    },
    humanizedAccuracy () {
      if (! this.find.findSpot || ! this.find.findSpot.location || ! this.find.findSpot.location.accuracy) {
        return;
      }

      var acc = this.find.findSpot.location.accuracy

      switch (acc) {
        case 1:
          return "1 - 5m (GPS)"
        case 5:
          return "5 - 20m"
        case 20:
          return "20 - 50m"
        case 50:
          return "50 - 100m"
        case 100:
          return "100 - 500m"
        case 500:
          return "500 - 2000m"
        case 2000:
          return "Gemeenteniveau"
      }

      return acc
    },
    finder () {
      return window.publicUserInfo
    },
    canUserSeeDetails () {
      // The coordinates returned from the API are already transformed according to the role of the user
      if (! this.user || this.user.isGuest || ! this.user.id) {
        return false
      }

      return this.user.administrator || this.user.onderzoeker || this.user.id == this.finder.id
    },
    user () {
      return window.medeaUser
    },
    periodOverruled () {
      const periods = (this.find.object.productionEvent.productionClassification || [])
        .map(c => c.productionClassificationCulturePeople)
        .filter(Boolean)
      if (periods.length > 1 && !sameValues(periods)) {
        return 'onzeker'
      }
      return periods[0]
    }
  },
  attached () {
    if (this.validating) {
      setTimeout(function () {
        $('#dt-check-help').addClass('tooltip-show')
      }, 2000)
      setTimeout(function () {
        $('#dt-check-help').removeClass('tooltip-show')
      }, 6000)
    }
  },
  filters: {
    comma (n) {
      return n.toString().replace('.', ',')
    },
    fromDate
  },
  components: {
    DtCheck
  }
}
</script>