<template>
  <h4 v-if="detail=='all'">Vondst</h4>
  <dl v-if="find.findDate&&detail=='all'">  
    <dt>Datum</dt>
    <dd>{{find.findDate}}</dd>
  </dl>
  <dl v-if="find.findSpot&&detail=='all'">  
    <dt>Locatie</dt>
    <dd>{{find.findSpot.location.address.locality}}</dd>
  </dl>
  <dl v-if="find.finder&&detail=='all'">  
    <dt>Vinder</dt>
    <dd>{{find.finder.name||'Niet zichtbaar'}}</dd>
  </dl>
  <h4 v-if="detail=='all'">Object</h4>
  <dl v-if="find.object.description&&detail=='all'">  
    <dt>Beschrijving</dt>
    <dd>{{find.object.description}}</dd>
  </dl>
  <dl v-if="find.object.objectInscription&&find.object.objectInscription.objectInscriptionNote">  
    <dt>Opschrift</dt>
    <dd>{{find.object.objectInscription.objectInscriptionNote}}</dd>
  </dl>
  <dl v-if="find.object.dimensions && find.object.dimensions.length && detail=='all'">
    <dt>Dimensies</dt>
    <dd v-for="dim in find.object.dimensions">{{dim.type}}: {{dim.value}}{{dim.unit}}</dd>
  </dl>
  <dl v-if="find.object.objectMaterial">
    <dt>Materiaal</dt>
    <dd>{{find.object.objectMaterial}}</dd>
  </dl>
  <dl v-if="find.object.productionEvent&&find.object.productionEvent.productionTechnique&&find.object.productionEvent.productionTechnique.type&&find.object.productionEvent.productionTechnique.type.length">
    <dt>Techniek</dt>
    <dd>{{find.object.productionEvent.productionTechnique.type}}</dd>
  </dl>
  <dl v-if="find.object.treatmentEvent&&find.object.treatmentEvent.modificationTechnique&&find.object.treatmentEvent.modificationTechnique.modificationTechniqueType">
    <dt>Behandeling</dt>
    <dd>{{find.object.treatmentEvent.modificationTechnique.modificationTechniqueType}}</dd>
  </dl>
  <dl v-if="find.object.category">
    <dt>Categorie</dt>
    <dd>{{find.object.category}}</dd>
  </dl>
  <dl v-if="find.object.period">
    <dt>Periode</dt>
    <dd>{{find.object.period}}</dd>
  </dl>
</template>

<script>
import ObjectFeatures from './ObjectFeatures';

export default {
  props: ['find', 'detail']
}
</script>