<template>
  <article>
    <div class="fe-imglist">
      <div class="fe-imglist-abs">
        <img :src="src.identifier || src" v-for="src in find.object.images">
      </div>
    </div>
    <section class="ui container fe-summary">
      <div class="ui two columns doubling grid">
        <div class="four wide column">
          <p>
            #{{find.identifier}} {{find.object.category}} {{find.object.material}} {{find.object.category}}
          </p>
          <object-features :find="find" detail="all"></object-features>
        </div>
        <div class="twelve wide column" v-if="user.isValidator&&find.object.objectValidationStatus == 'in bewerking'">
          <validation-form :obj="find.object.identifier"></validation-form>
        </div>
        <div class="twelve wide column" v-else>
          <classification v-for="prod in find.object.productionEvent" :cls="prod.classification" :obj="find.object.identifier"></classification>
          <div class="ui orange message" v-if="!find.object.productionEvent">
            <div class="ui header">Deze vondst is niet geclassificeerd</div>
            <p v-if="user.isFindExpert">Voeg jij een classificatie toe?</p>
          </div>
          <add-classification :object="find.object" v-if="user.isFindExpert"></add-classification>
        </div>
      </div>
    </section>
  </article>
</template>

<script>
import checkbox from 'semantic-ui-css/components/checkbox.min.js';

import AddClassification from './AddClassification';
import Classification from './Classification';
import ObjectFeatures from './ObjectFeatures';
import ValidationForm from './ValidationForm';

export default {
  props: ['user', 'find'],
  data () {
    return {
      show: {
        validation: false
      }
    }
  },
  components: {
    AddClassification,
    Classification,
    ObjectFeatures,
    ValidationForm,
  }
}
</script>