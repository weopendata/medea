<template>
  <article>
    <div class="fe-header">
      <div class="fe-imglist">
        <div class="img" v-for="src in find.object.photograph">
          <photoswipe-thumb :src="src" :index="$index"></photoswipe-thumb>
          <span class="fe-img-remark" v-if="user.validator&&find.object.objectValidationStatus == 'in bewerking'" @click="imgRemark($index)">Opmerking toevoegen</span>
        </div>
      </div>
      <div class="fe-header-fixed">
        <h1>
          #{{find.identifier}} {{find.object.category}} {{find.object.objectMaterial}} {{find.object.productionEvent.productionTechnique.type}}
        </h1>
      </div>
    </div>
    <section class="ui container fe-summary">
      <div class="ui two columns doubling grid">
        <div class="four wide column">
          <object-features :find="find" detail="all"></object-features>
          <a class="ui basic small icon black button" href="/finds/{{find.identifier}}/edit" v-if="(user.email==find.person.email)||user.validator">
            <i class="pencil icon"></i>
            Bewerken
          </a>
        </div>
        <div class="twelve wide column">
          <div v-if="user.validator&&find.object.objectValidationStatus == 'in bewerking'">
            <validation-form :obj="find.object.identifier"></validation-form>
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
import PhotoswipeThumb from './PhotoswipeThumb';

export default {
  props: ['user', 'find'],
  data () {
    return {
      show: {
        validation: false
      }
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
          src: img.identifier,
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