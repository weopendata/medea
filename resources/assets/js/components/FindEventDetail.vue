<template>
  <article>
    <div class="fe-imglist">
      <div class="fe-imglist-abs">
        <img :src="'/uploads/' + src" v-for="src in find.object.images">
        <div class="fe-imglist-feedback ui form">
          <h3>Feedback</h3>
          <div class="field">
            <div class="ui checkbox">
              <input type="checkbox" tabindex="0" class="hidden" v-model="photoValidation" value="onscherp">
              <label>onscherp</label>
            </div>
          </div>
          <div class="field">
            <div class="ui checkbox">
              <input type="checkbox" tabindex="0" class="hidden" v-model="photoValidation" value="te kleine resolutie">
              <label>te kleine resolutie</label>
            </div>
          </div>
          <div class="field">
            <div class="ui checkbox">
              <input type="checkbox" tabindex="0" class="hidden" v-model="photoValidation" value="onvoldoende ingezoomd">
              <label>onvoldoende ingezoomd</label>
            </div>
          </div>
          <div class="field">
            <div class="ui checkbox">
              <input type="checkbox" tabindex="0" class="hidden" v-model="photoValidation" value="teveel ingezoomd">
              <label>teveel ingezoomd</label>
            </div>
          </div>
          <div class="field">
            <div class="ui checkbox">
              <input type="checkbox" tabindex="0" class="hidden" v-model="photoValidation" value="overbelicht">
              <label>over/onderbelicht</label>
            </div>
          </div>
          <button class="ui green button" type="submit" v-if="!photoValidation.length">Foto's goedkeuren</button>
          <button class="ui blue button" type="submit" v-if="photoValidation.length">Feedback versturen</button>
        </div>
      </div>
    </div>
    <section class="fe-summary">
      <div class="ui two columns doubling grid">
        <div class="four wide column">
          <object-features :find="find" detail="all"></object-features>
        </div>
        <div class="twelve wide column">
          <classification v-for="cls in find.object.productionEvent" :cls="cls"></classification>
        </div>
      </div>
    </section>
  </article>
</template>

<script>
import checkbox from 'semantic-ui-css/components/checkbox.min.js';
import ObjectFeatures from './ObjectFeatures';
import Classification from './Classification';

export default {
  props: ['user', 'find'],
  components: {
    ObjectFeatures,
    Classification
  },
  data () {
    return {
      findEvents: [],
      photoValidation: []
    }
  },
  computed: {
    thumb () {
      return this.find.object.images && ('/uploads/' + this.find.object.images[0]);
    }
  },
  ready () {
    $('.ui.checkbox').checkbox()
  }
}
</script>