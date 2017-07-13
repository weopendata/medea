<template>
  <div>
    <div v-if="!cls || !cls.productionClassificationType">
      <div class="field">
        <label>Nieuwe classificatie</label>
        <button class="ui blue button" @click.prevent="setMainType('Typologie')">Typologie</button>
        <button class="ui button" @click.prevent="setMainType('Gelijkaardige vondst')">Gelijkaardige vondst</button>
      </div>
    </div>
    <div v-else>
      <div class="required field">
        <label>{{ isTypology ? 'Type' : 'Vindplaats' }}</label>
        <input type="text" v-model="cls.productionClassificationValue" :list="isTypology && 'types'">
        <div class="help-block">{{ isTypology ? 'Vul hier de naam van het type in, zoals weergegeven in de literatuur (waar je naar verwijst in het veld \'Bron\').' : 'Vul hier de naam in van de vindplaats van de gelijkaardige vondst.' }}</div>
      </div>
      <div class="two fields" @change="limitPeriod">
        <div class="field" @change="limitDateRange">
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
      <div class="two fields" @change="limitPeriod">
        <div class="field" :class="{error: validRange, required: isTypology}">
          <label>Datering van</label>
          <input-date :model.sync="cls.startDate"></input-date>
          <div class="help-block">{{ isTypology ? 'Vul hier de startdatum in van dit type, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\').' : 'Vul hier de startdatum van de gelijkaardige vondst in, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\') (optioneel).' }}</div>
        </div>
        <div class="field" :class="{error: validRange, required: isTypology}">
          <label>Datering tot</label>
          <input-date :model.sync="cls.endDate"></input-date>
          <div class="help-block">{{ isTypology ? 'Vul hier de einddatum in van dit type, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\').' : 'Vul hier de einddatum van de gelijkaardige vondst in, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\') (optioneel.)' }}</div>
        </div>
      </div>
      <div class="field field-publications">
        <div class="ui grid">
          <div class="twelve wide column">
            <label for="description">Bron</label>
            <div class="help-block">
              {{ isTypology ? "Vul hier de publicatie in die het type beschrijft. Typ de auteursnaam of titel in, en kies de correcte publicatie. Als de publicatie nog niet in de lijst staat, klik dan op de knop 'bron toevoegen' om deze toe te voegen. Je kunt meerdere publicaties linken aan een classificatie." : "Vul hier de publicatie in waarin de gelijkaardige vondst beschreven staat. Typ de auteursnaam of titel in, en kies de correcte publicatie. Als de publicatie nog niet in de lijst staat, klik dan op de knop 'bron toevoegen' om deze toe te voegen. Je kunt meerdere publicaties linken aan een classificatie." }}
            </div>
          </div>
          <div class="four wide column" v-if="cls.publication && cls.publication.length">
            <label for="description">Specificatie</label>
            <div class="help-block">
              Preciseer hier een locatie (pagina, figuur) in de aangehaalde bron (optioneel).
            </div>
          </div>
        </div>
        <div class="ui grid" v-for="(index, pub) in cls.publication" style="margin-top:0;">
          <div class="twelve wide column">
            <input-publication :model="pub" :index="index"></input-publication>
          </div>
          <div class="four wide column">
            <input type="text" :value="getSource(index)" @input="setSource(index, $event.target.value)">
          </div>
        </div>
        <br>
        <select-publication :model="pub"></select-publication>
        <button type="button" class="ui gray button" @click="addPublication">Bron toevoegen</button>
      </div>
      <div class="field">
        <label for="description">Opmerking</label>
        <textarea-growing id="description" :model.sync="cls.productionClassificationDescription"></textarea-growing>
        <div class="help-block">
          Voeg hier eventueel een opmerking toe aan je classificatie (optioneel).
        </div>
      </div>
    </div>

    <div class="ui dimmer modals page transition visible active" v-if="editing" @click="closePublication">
      <div class="ui modal transition visible active" @click.stop>
        <div class="header">
          <h2>Publicatie bewerken</h2>
        </div>
        <div class="content">
          <div class="two fields">
            <div class="required field">
              <label>Titel</label>
              <input type="text" v-model="editing.publicationTitle">
            </div>
            <div class="required field">
              <label>Type</label>
              <select class="ui dropdown" v-model="editing.publicationType">
                <option>boek</option>
                <option>tijdschriftartikel</option>
                <option>boekbijdrage</option>
                <option>internetbron</option>
              </select>
            </div>
          </div>
          <div class="required field">
            <label>Auteurs (meerdere namen gescheiden door komma)</label>
            <input type="text" v-model="editing.author">
          </div>
          <div class="three fields">
            <div class="field">
              <label>Jaar van uitgave</label>
              <input type="text" v-model="editing.pubTimeSpan">
            </div>
            <div class="field">
              <label>Plaats van uitgave</label>
              <input type="text" v-model="editing.pubLocation">
            </div>
            <div class="field">
              <label>Pagina's</label>
              <input type="text" v-model="editing.publicationVolume">
            </div>
          </div>
          <div class="field">
            <label>URL van de publicatie</label>
            <input type="text" v-model="editing.publicationContact">
          </div>
          <br>
        </div>
        <div class="actions">
          <div class="ui {{ validPublication ? 'green' : 'grey disabled' }} button" @click="savePublication">
            <i class="checkmark icon"></i>
            Bewaren
          </div>
          <div class="ui gray button" @click="rmPublication">
            <i class="unlinkify icon"></i>
            Referentie verwijderen
          </div>
        </div>
      </div>
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
import InputPublication from './InputPublication.vue'
import SelectPublication from './SelectPublication.vue'

import { fromPublication, toPublication } from '../const.js'

const dateRanges = [
  { period: 'Bronstijd', min: -2000, max: -801 },
  { period: 'IJzertijd', min: -800, max: -58 },
  { period: 'Romeins', min: -57, max: 400 },
  { period: 'vroegmiddeleeuws', min: 401, max: 900 },
  { period: 'middeleeuws', min: 401, max: 1500 },
  { period: 'volmiddeleeuws', min: 901, max: 1500 },
  { period: 'laatmiddeleeuws', min: 1201, max: 1500 },
  { period: 'postmiddeleeuws', min: 1501, max: 1900 },
  { period: 'modern', min: 1901, max: new Date().getFullYear() },
  { period: 'Wereldoorlog I', min: 1914, max: 1918 },
  { period: 'Wereldoorlog II', min: 1940, max: 1945 }
]

export default {
  props: ['cls'],
  data () {
    return {
      editing: null,
      fields: window.fields.classification,
      types: ['2.1', '2.2', '2.3']
    }
  },
  computed: {
    isTypology () {
      return this.cls.productionClassificationType === 'Typologie'
    },
    validRange () {
      return parseInt(this.cls.startDate) > parseInt(this.cls.endDate)
    },
    validPublication () {
      return this.isValidPublication(this.editing)
    }
  },
  methods: {
    setMainType (type) {
      this.cls.productionClassificationType = type
    },
    limitDateRange () {
      const period = this.cls.productionClassificationCulturePeople
      const range = dateRanges.find(r => r.period === period)
      if (range) {
        if (range.min && (!this.cls.startDate || this.cls.startDate < range.min || this.cls.startDate >= range.max)) {
          this.cls.startDate = range.min
        }
        if (range.max && (!this.cls.endDate || this.cls.endDate > range.max || this.cls.endDate <= range.min)) {
          this.cls.endDate = range.max
        }
      }
    },
    limitPeriod () {
      if (!this.cls.startDate || !this.cls.endDate) {
        return
      }
      const year = (parseInt(this.cls.startDate) + parseInt(this.cls.endDate)) / 2
      const range = dateRanges.find(r => r.min < year && r.max > year)
      if (range) {
        this.cls.productionClassificationCulturePeople = range.period
      }
      console.log('set', year, range && range.period)
    },
    getSource (index) {
      const source = this.cls.productionClassificationSource
      return source && source[index] || ''
    },
    setSource (index, value) {
      if (!this.cls.productionClassificationSource || typeof this.cls.productionClassificationSource !== 'object') {
        this.$set('cls.productionClassificationSource', {})
      }
      this.$set('cls.productionClassificationSource[' + index + ']', value)
    },
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
    },
    isValidPublication (pub) {
      return pub && pub.publicationTitle && pub.publicationType && pub.author
    },
    editPublication (pub, index) {
      this.editing = fromPublication(pub)
      this.editingIndex = index
    },
    closePublication () {
      if (!this.isValidPublication(this.cls.publication[this.editingIndex])) {
        this.rmPublication(this.editingIndex)
      }
      this.editing = null
      this.editingIndex = -1
    },
    savePublication () {
      this.$set('cls.publication[editingIndex]', toPublication(this.editing))
      this.closePublication()
    },
    addPublication (pub) {
      pub = pub || {}
      this.cls.publication.push(pub)
      this.editPublication(pub, this.cls.publication.length - 1)
    },
    rmPublication (index) {
      if (typeof index === 'number') {
        this.editingIndex = index
      }
      if (this.cls.productionClassificationSource) {
        this.cls.productionClassificationSource[this.editingIndex] = null
      }
      this.cls.publication.splice(this.editingIndex, 1)
      this.editing = null
      this.editingIndex = -1
    },
    keydown (evt) {
      evt = evt || window.event;
      if (evt.keyCode == 27) {
        this.closePublication()
      }
    }
  },
  attached () {
    if (!this.cls.publication) {
      this.$set('cls.publication', [])
    }
    // $('select.ui.dropdown').dropdown()
  },
  created: function() {
    window.addEventListener('keydown',this.keydown);
  },
  destroyed: function() {
    window.removeEventListener('keydown', this.keydown);
  },
  components: {
    InputDate,
    InputPublication,
    SelectPublication,
    TextareaGrowing
  }
}
</script>