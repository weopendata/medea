<template>
  <div>
    <div class="two fields">
      <div class="field">
        <label>Type</label>
        <input type="text" v-model="cls.productionClassificationType" placeholder="Voorbeeld: 2.1" list="types">
        <div class="help-block">Welk type classificatie doe je?</div>
      </div>
      <div class="field">
        <label>Periode</label>
        <select class="ui search fluid dropdown" v-model="cls.productionClassificationCulturePeople">
          <option>onbekend</option>
          <option v-for="opt in fields.culturepeople" :value="opt" v-text="opt"></option>
        </select>
        <div class="help-block">Uit welke archeologische periode komt het object?</div>
      </div>
      <div class="field">
        <label>Heerser</label>
        <input type="text" v-model="cls.productionClassificationRulerNation" placeholder="(Alleen voor munten)" list="nations">
        <div class="help-block">Alleen voor munten: Welke heerser was destijds aan de macht?</div>
      </div>
    </div>
    <div class="two fields">
      <div class="field">
        <label>Datering vanaf</label>
        <input-date :model.sync="cls.startDate">
      </div>
      <div class="field">
        <label>Datering tot</label>
        <input-date :model.sync="cls.endDate">
      </div>
    </div>
    <div class="field">
      <label for="description">Referenties</label>
      <input type="text" v-model="pub.publicationTitle" placeholder="bibliografische referentie, URL, DOI, ..." v-for="pub in cls.publication" @input="pubCheck" track-by="$index">
      <div class="help-block">
        Vul verwijzingen in naar publicaties die je tot deze classificatie gebracht hebben.
        <br>
      </div>
    </div>
    <div class="field">
      <label for="description">Opmerkingen</label>
      <textarea-growing id="description" :model.sync="cls.productionClassificationDescription"></textarea-growing>
    </div>
    <datalist id="types">
      <option v-for="opt in fields.type" :value="opt"></option>
    </datalist>
    <datalist id="nations">
      <option value="Napoleon">
      <option value="Caesar">
      <option value="Cleopatra">
    </datalist>
  </div>
</template>

<script>
import TextareaGrowing from './TextareaGrowing';
import InputDate from './InputDate';

export default {
  props: ['cls'],
  data () {
    return {
      fields: window.fields.classification,
      types: ['2.1', '2.2', '2.3']
    }
  },
  methods: {
    pubCheck () {
      var empties = 0;
      for (var i = 0; i < this.cls.publication.length; i++) {
        empties += this.cls.publication[i].publicationTitle && this.cls.publication[i].publicationTitle.length ? 0 : 1
      }
      if(!empties) {
        this.$nextTick(function () {
          this.cls.publication.push({publicationTitle: ''})
        })
      } else if (empties > 1) {
        this.$nextTick(function () {
          for (var i = 0; i < this.cls.publication.length; i++) {
            if (!this.cls.publication[i].publicationTitle.length) {
              this.cls.publication.splice(i, 1)
              break
            }
          }
        })
      }
    }
  },
  attached () {
    if (!this.cls.publication) {
      this.$set('cls.publication', [{publicationTitle: ''}])
    }
    // $('select.ui.dropdown').dropdown()
  },
  components: {
    InputDate,
    TextareaGrowing
  }
}
</script>