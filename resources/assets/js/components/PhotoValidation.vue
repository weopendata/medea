<template>
  <div>
    <div class="field">
      <label>Opmerkingen bij foto {{num}}</label>
    </div>
    <div class="fields">
      <div class="four wide field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" class="hidden" v-model="model" value="meetschaal">
          <label>geen meetschaal in beeld</label>
        </div>
      </div>
      <div class="four wide field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" class="hidden" v-model="model" value="onscherp">
          <label>onscherp</label>
        </div>
      </div>
      <div class="four wide field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" class="hidden" v-model="model" value="onderbelicht">
          <label>onderbelicht</label>
        </div>
      </div>
      <div class="four wide field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" class="hidden" v-model="model" value="onvoldoende ingezoomd">
          <label>onvoldoende ingezoomd</label>
        </div>
      </div>
    </div>
    <div class="fields">
      <div class="four wide field">
      </div>
      <div class="four wide field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" class="hidden" v-model="model" value="te kleine resolutie">
          <label>te kleine resolutie</label>
        </div>
      </div>
      <div class="four wide field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" class="hidden" v-model="model" value="overbelicht">
          <label>overbelicht</label>
        </div>
      </div>
      <div class="four wide field">
        <div class="ui checkbox">
          <input type="checkbox" tabindex="0" class="hidden" v-model="model" value="teveel ingezoomd">
          <label>teveel ingezoomd</label>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    'index': true,
    'model': {
      default: []
    }
  },
  computed: {
    num () {
      return parseInt(this.index) + 1
    }
  },
  ready () {
    $('.ui.checkbox').checkbox()
  }
}
</script>