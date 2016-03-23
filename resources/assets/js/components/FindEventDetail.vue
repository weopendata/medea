<template>
  <article>
    <div class="fe-imglist">
      <div class="fe-imglist-abs">
        <img :src="src.identifier || src" v-for="src in find.object.images">
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
          <div class="field">
            <div class="ui checkbox">
              <input type="checkbox" tabindex="0" class="hidden" v-model="photoValidation" value="meetschaal">
              <label>geen meetschaal in beeld</label>
            </div>
          </div>
          <button class="ui green button" type="submit" v-if="!photoValidation.length">Foto's goedkeuren</button>
          <button class="ui orange button" type="submit" v-if="photoValidation.length">Feedback versturen</button>
        </div>
      </div>
    </div>
    <section class="ui container fe-summary">
      <div class="ui two columns doubling grid">
        <div class="four wide column">
          <object-features :find="find" detail="all"></object-features>
        </div>
        <div class="twelve wide column">
          <classification v-for="cls in find.object.productionEvent" :cls="cls"></classification>
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
import ObjectFeatures from './ObjectFeatures';
import Classification from './Classification';
import AddClassification from './AddClassification';

export default {
  props: ['user', 'find'],
  data () {
    return {
      photoValidation: []
    }
  },
  ready () {
    $('.ui.checkbox').checkbox()
  },
  components: {
    AddClassification,
    Classification,
    ObjectFeatures
  }
}
</script>