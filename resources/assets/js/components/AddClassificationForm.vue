<template>
  <div>
    <div class="two fields">
      <div class="field">
        <label>Type</label>
        <input type="text" v-model="cls.type" placeholder="Voorbeeld: 2.1" list="types">
      </div>
      <div class="field">
        <label>Periode</label>
        <select class="ui search fluid dropdown" v-model="cls.period">
          <option>onbekend</option>
          <option v-for="opt in fields.period" :value="opt" v-text="opt"></option>
        </select>
      </div>
      <div class="field">
        <label>Heerser</label>
        <input type="text" v-model="cls.ruler" placeholder="(Alleen voor munten)" list="rulers">
      </div>
    </div>
    <div class="two fields">
      <div class="field">
        <label>Vanaf</label>
        <input-date :model.sync="cls.startDate">
      </div>
      <div class="field">
        <label>Tot</label>
        <input-date :model.sync="cls.endDate">
      </div>
    </div>
    <div class="field">
      <label for="description">Referenties</label>
      <input type="text" v-model="pub.title" placeholder="Vul een verwijzing in naar een publicatie (bibliografische referentie, URL, DOI, ...)" v-for="pub in cls.publication" @input="pubCheck" track-by="$index">
    </div>
    <div class="field">
      <label for="description">Opmerkingen</label>
      <textarea-growing id="description" :model.sync="cls.description"></textarea-growing>
    </div>
    <datalist id="types">
      <option v-for="opt in fields.type" :value="opt"></option>
    </datalist>
    <datalist id="rulers">
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
        empties += this.cls.publication[i].title.length ? 0 : 1
      }
      if(!empties) {
        this.$nextTick(function () {
          this.cls.publication.push({title: ''})
        })
      } else if (empties > 1) {
        this.$nextTick(function () {
          for (var i = 0; i < this.cls.publication.length; i++) {
            if (!this.cls.publication[i].title.length) {
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
      this.$set('cls.publication', [{title: ''}])
    }
    $('select.ui.dropdown').dropdown()
  },
  components: {
    InputDate,
    TextareaGrowing
  }
}
</script>