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
  <dl v-if="find.object.inscription">  
    <dt>Opschrift</dt>
    <dd>{{find.object.inscription}}</dd>
  </dl>
  <dl v-if="find.object.dimensions && find.object.dimensions.length">
    <dt>Dimensies</dt>
    <dd v-for="dim in find.object.dimensions">{{dim.type}}: {{dim.value}}{{dim.unit}}</dd>
  </dl>
  <dl v-if="find.object.material">
    <dt>Materiaal</dt>
    <dd>{{find.object.material}}</dd>
  </dl>
  <dl v-if="find.object.productionEvent&&find.object.productionEvent.productionTechnique">
    <dt>Techniek</dt>
    <dd>{{find.object.productionEvent.productionTechnique.type}}</dd>
  </dl>
  <dl v-if="find.object.category">
    <dt>Categorie</dt>
    <dd>{{find.object.category}}</dd>
  </dl>
</template>

<script>
import ObjectFeatures from './ObjectFeatures';

export default {
  props: ['find', 'detail']
}
</script>