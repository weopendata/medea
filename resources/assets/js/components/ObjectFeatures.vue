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
    <dd>{{find.findSpot.location.address.locationAddressLocality}}&nbsp;</dd>
  </dl>
  <dl v-if="finder&&finder.name">
    <dt-check v-if="validating" prop="finder.name"></dt-check>
    <dt>Vinder</dt>
    <dd>
      <a v-if="finder.id" :href="'/persons/'+finder.id" v-text="finder.name"></a>
      <span v-else v-text="finder.name"></span>
      &nbsp;
    </dd>
  </dl>
  <h4>Object</h4>
  <dl v-if="find.object.objectDescription">
    <dt-check v-if="validating" prop="objectDescription"></dt-check>
    <dt>Beschrijving</dt>
    <dd>{{find.object.objectDescription}}</dd>
  </dl>
  <dl v-if="find.object.objectInscription&&find.object.objectInscription.objectInscriptionNote">
    <dt-check v-if="validating" prop="objectInscriptionNote"></dt-check>
    <dt>Opschrift</dt>
    <dd>{{find.object.objectInscription.objectInscriptionNote}}</dd>
  </dl>
  <dl v-if="find.object.dimensions && find.object.dimensions.length">
    <dt-check v-if="validating" prop="dimensions"></dt-check>
    <dt>Dimensies</dt>
    <dd v-for="dim in find.object.dimensions">{{dim.dimensionType}}: {{dim.measurementValue|comma}}{{dim.dimensionUnit}}</dd>
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
    <dd>{{find.object.period}}</dd>
  </dl>
  <h4>Details</h4>
  <dl v-if="find.updated_at!==find.created_at">
    <dt>Gewijzigd op</dt>
    <dd>{{find.updated_at | fromDate}}</dd>
  </dl>
  <dl>
    <dt>Toegevoegd op</dt>
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
</template>

<script>
import DtCheck from './DtCheck.vue'
import {fromDate} from '../const.js'

export default {
  props: ['find', 'feedback', 'validating'],
  computed: {
    finder () {
      return window.publicUserInfo
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