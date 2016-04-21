<template>
  <article>
    <div class="fe-header">
      <div class="fe-imglist">
        <div class="img" v-for="src in find.object.images">
          <photoswipe-thumb :src="src" :index="$index"></photoswipe-thumb>
          <span class="fe-img-remark" v-if="user.validator&&find.object.objectValidationStatus == 'in bewerking'" @click="imgRemark($index)">Opmerking toevoegen</span>
        </div>
      </div>
      <div class="fe-header-fixed">
        <h1>
          #{{find.identifier}} {{find.object.category}} {{find.object.material}} {{find.object.productionEvent.productionTechnique.type}}
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
        <div class="twelve wide column" v-if="user.validator&&find.object.objectValidationStatus == 'in bewerking'">
          <validation-form :obj="find.object.identifier"></validation-form>
        </div>
        <div class="twelve wide column" v-if="find.object.objectValidationStatus == 'gevalideerd'">
          <classification v-for="cls in find.object.productionEvent.classification" :cls="cls" :obj="find.object.identifier"></classification>
          <div class="ui orange message" v-if="!find.object.productionEvent&&!find.object.productionEvent.classification&&!find.object.productionEvent.classification.length">
            <div class="ui header">Deze vondst is niet geclassificeerd</div>
            <p v-if="user.expert">Voeg jij een classificatie toe?</p>
          </div>
          <add-classification :object="find.object" v-if="user.expert"></add-classification>
        </div>
        <div class="twelve wide column" v-if="!user.validator&&find.object.objectValidationStatus !== 'gevalideerd'">
          Security error #20984
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
      var items = this.find.object.images.map(img => {
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