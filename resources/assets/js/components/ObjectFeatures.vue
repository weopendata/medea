<template>
  <h4 v-if="detail=='all'">Vondst</h4>
  <dl v-if="detail=='all'">  
    <dt-check v-if="validating" prop="findDate" data-tooltip="Duid aan wat gewijzigd moet worden" data-position="top left" data-green="" id="dt-check-help"></dt-check>
    <dt>Datum</dt>
    <dd>{{find.findDate || 'niet beschikbaar'}}</dd>
  </dl>
  <dl v-if="find.findSpot&&detail=='all'">
    <dt-check v-if="validating" prop="location"></dt-check>
    <dt>Locatie</dt>
    <dd>{{find.findSpot.location.address.locality}}</dd>
  </dl>
  <dl v-if="find.finder&&detail=='all'">
    <dt-check v-if="validating" prop="finder.name"></dt-check>
    <dt>Vinder</dt>
    <dd>{{find.finder.name||'Niet zichtbaar'}}</dd>
  </dl>
  <h4 v-if="detail=='all'">Object</h4>
  <dl v-if="find.object.objectDescription&&detail=='all'">
    <dt-check v-if="validating" prop="objectDescription"></dt-check>
    <dt>Beschrijving</dt>
    <dd>{{find.object.objectDescription}}</dd>
  </dl>
  <dl v-if="find.object.objectInscription&&find.object.objectInscription.objectInscriptionNote">
    <dt-check v-if="validating" prop="objectInscriptionNote"></dt-check>
    <dt>Opschrift</dt>
    <dd>{{find.object.objectInscription.objectInscriptionNote}}</dd>
  </dl>
  <dl v-if="find.object.dimensions && find.object.dimensions.length && detail=='all'">
    <dt-check v-if="validating" prop="dimensions"></dt-check>
    <dt>Dimensies</dt>
    <dd v-for="dim in find.object.dimensions">{{dim.dimensionType}}: {{dim.measurementValue}}{{dim.dimensionUnit}}</dd>
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
</template>

<script>
import DtCheck from './DtCheck.vue'

export default {
  props: ['find', 'detail', 'feedback', 'validating'],
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
  components: {
    DtCheck
  }
}
</script>