<template>
  <article>
    <div class="ui container fe-fiche"> 
      <h1>#{{find.identifier}} {{find.object.objectCategory}} {{find.object.period}} {{find.object.objectMaterial}}</h1>
      <div class="ui two columns stackable grid">
        <div class="column" :class="{'fe-validating':validating}">
          <object-features :find="find" detail="all" :validation="validation" :validating="validating"></object-features>
          <a class="ui basic small icon black button" href="/finds/{{find.identifier}}/edit" v-if="(user.email==find.person.email)||user.validator">
            <i class="pencil icon"></i>
            Bewerken
          </a>
        </div>
        <div class="column">
          <div class="fe-header">
            <div class="fe-imglist">
              <div class="img" v-for="image in find.object.photograph">
                <photoswipe-thumb :image="image" :index="$index"></photoswipe-thumb>
                <span class="fe-img-remark" v-if="validating" @click="imgRemark($index)">Opmerking toevoegen</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <br>
    <div class="ui container">
          <div v-if="validating">
            <validation-form :obj="find.object.identifier" :validation="validation"></validation-form>
          </div>
          <div v-if="find.object.objectValidationStatus == 'gevalideerd'">
            <classification v-for="cls in find.object.productionEvent.productionClassification" :cls="cls" :obj="find.object.identifier"></classification>
            <div class="ui orange message" v-if="!find.object.productionEvent&&!find.object.productionEvent.productionClassification&&!find.object.productionEvent.productionClassification.length">
              <div class="ui header">Deze vondst is niet geclassificeerd</div>
              <p v-if="user.expert">Voeg jij een classificatie toe?</p>
            </div>
            <add-classification :object="find.object" v-if="user.expert"></add-classification>
          </div>
          <h1 v-if="!user.validator&&find.object.objectValidationStatus !== 'gevalideerd' && (user.email!==find.person.email)">
            Security error #20984
          </h1>
          <div v-if="find.object.objectValidationStatus == 'embargo'">Deze vondst is onder embargo</div>
          <div v-if="(user.email==find.person.email)">
            <div v-if="find.object.objectValidationStatus == 'in bewerking'">Je vondstfiche wordt gevalideerd</div>
            <div v-if="find.object.objectValidationStatus == 'revisie nodig'">Ofwel is dit een draft, ofwel is er feedback die wijzigingen aan deze vondstfiche gebieden.</div>
          </div>
          <div v-else>
            <div v-if="find.object.objectValidationStatus == 'in bewerking'">Deze vondstfiche wordt gevalideerd</div>
            <div v-if="find.object.objectValidationStatus == 'revisie nodig'">Deze vondstfiche is in revisie</div>
          </div>
          <div v-if="find.object.objectValidationStatus == 'afgekeurd'">
            Deze vondstfiche is niet geschikt voor MEDEA.
          </div> 
    </div>
  </article>
</template>

<script>
import checkbox from 'semantic-ui-css/components/checkbox.min.js';

import AddClassification from './AddClassification';
import Classification from './Classification';
import ObjectFeatures from './ObjectFeatures';
import ValidationForm from './ValidationForm';
import PhotoswipeThumb from './PhotoswipeThumb';

export default {
  props: ['user', 'find'],
  data () {
    return {
      validation: {},
      show: {
        validation: false
      }
    }
  },
  computed: {
    validating () {
      return this.user.validator && this.find.object.objectValidationStatus == 'in bewerking'
    }
  },
  methods: {
    imgRemark (index) {
      this.$broadcast('imgRemark', index)
    }
  },
  events: {
    initPhotoswipe (options) {
      if (!window.PhotoSwipe) {
        return console.warn('PhotoSwipe missing')
      }
      var pswpElement = document.querySelector('.pswp');
      var items = this.find.object.photograph.map(img => {
        return {
          src: img.src,
          msrc: img.resized,
          w: img.width || 1600,
          h: img.height || 900
        }
      })
      var gallery = new window.PhotoSwipe(pswpElement, window.PhotoSwipeUI_Default, items, options);
      gallery.init();
    }
  },
  components: {
    AddClassification,
    Classification,
    ObjectFeatures,
    ValidationForm,
    PhotoswipeThumb,
  }
}
</script>