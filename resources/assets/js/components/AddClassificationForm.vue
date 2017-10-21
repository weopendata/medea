<template>
  <div>
    <div v-if="!cls || !cls.productionClassificationType">
      <div class="field">
        <label>Nieuwe classificatie</label>
        <button class="ui blue button" @click.prevent="setMainType('Typologie')">Typologie</button>
        <button class="ui button" @click.prevent="setMainType('Gelijkaardige vondst')">Gelijkaardige vondst</button>
        <button class="ui button" @click.prevent="setMainType('Publicatie van deze vondst')">Publicatie van deze vondst</button>
      </div>
      <div class="help-block">
          Kies hier welk soort informatie je wil toevoegen aan deze vondstfiche. 'Typologie' laat je toe om deze vondst toe te wijzen aan een bepaald objecttype. Je kunt ook verwijzen naar een 'Gelijkaardige vondst', bijvoorbeeld afkomstig uit een opgraving. In beide gevallen kun je de datering preciseren en verwijzen naar een boek, internetpagina of andere gepubliceerde bron. Als de vondst op deze fiche zelf in detail beschreven en besproken werd in een publicatie, kies dan voor de laatste optie 'Publicatie van deze vondst'. Zo kun je een verwijzing naar die bron toevoegen, en de voornaamste informatie uit die bron aan deze fiche koppelen.
      </div>
    </div>
    <div v-else>
      <!-- Find place -->
      <div class="required field">
        <label>{{ isSourceRequired ? 'Vindplaats/Type' : isTypology ? 'Type' : 'Vindplaats' }}</label>
        <input type="text" v-model="cls.productionClassificationValue" :list="isTypology && 'types'">
        <div class="help-block">{{ isSourceRequired ? 'Vul hier in aan welk type de auteur deze vondst toewijst, en/of met welke andere vondst(en) hij het voorwerp vergelijkt (optioneel).' : isTypology ? 'Vul hier de naam van het type in, zoals weergegeven in de literatuur (waar je naar verwijst in het veld \'Bron\').' : 'Vul hier de naam in van de vindplaats van de gelijkaardige vondst.' }}</div>
      </div>
      <!-- Period & Ruler -->
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
      <!-- Date -->
      <div class="two fields" @change="limitPeriod">
        <div class="field" :class="{error: validRange, required: isTypology}">
          <label>Datering van</label>
          <input-date :model.sync="cls.startDate"></input-date>
          <div class="help-block">{{ isSourceRequired ? 'Vul hier de startdatum in van de datering die de auteur aan deze vondst toewijst.' : isTypology ? 'Vul hier de startdatum in van dit type, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\').' : 'Vul hier de startdatum van de gelijkaardige vondst in, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\') (optioneel).' }}</div>
        </div>
        <div class="field" :class="{error: validRange, required: isTypology}">
          <label>Datering tot</label>
          <input-date :model.sync="cls.endDate"></input-date>
          <div class="help-block">{{ isSourceRequired ? 'Vul hier de startdatum/einddatum in van de datering die de auteur aan deze vondst toewijst.' : isTypology ? 'Vul hier de einddatum in van dit type, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\').' : 'Vul hier de einddatum van de gelijkaardige vondst in, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\') (optioneel.)' }}</div>
        </div>
      </div>
     <!-- Source -->
      <div class="field field-publications" v-if="! isSourceRequired">
        <div class="ui grid">
          <div class="twelve wide column">
            <label for="description">Bron
              <span v-if="isSourceRequired" class="required">*</span>
            </label>
            <div class="help-block">
              Verwijs hier naar de publicatie waarin deze vondst beschreven staat.
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
      <!-- Remark -->
      <div class="field">
        <label for="description">Opmerking</label>
        <textarea-growing id="description" :model.sync="cls.productionClassificationDescription"></textarea-growing>
        <div class="help-block">
          Voeg hier eventueel een opmerking toe aan je classificatie (optioneel).
        </div>
      </div>
    </div>

    <!-- Add publication -->
    <div class="ui dimmer modals page transition visible active" v-if="editing" @click="closePublication">
      <div class="ui modal transition visible active" @click.stop>
        <div class="header">
          <h2>Publicatie bewerken</h2>
        </div>
        <div class="content">
          <div class="two fields">
            <div class="required field">
              <label v-on:mouseover="this.tt.title = ! this.tt.title">Titel</label>
              <input type="text" v-model="editing.publicationTitle" :placeholder="placeholder('title')">
            </div>
            <div class="required field">
              <label>Type publicatie</label>
              <select class="ui dropdown" v-model="editing.publicationType">
                <option selected>boek</option>
                <option>tijdschriftartikel</option>
                <option>boekbijdrage</option>
                <option>internetbron</option>
              </select>
            </div>
          </div>
          <div class="one field" v-if="editing.publicationType == 'tijdschriftartikel'">
            <div class="required field">
              <label>Volume title</label>
              <input type="text" v-model="editing.publicationVolume" placeholder="Het volume of de jaargang van het tijdschrift.">
            </div>
          </div>
          <div :class="editing.publicationType == 'internetbron' ? 'field' : 'required field'">
            <label>Namen van de auteurs</label>
            <input type="text" v-model="editing.author" >
            <small class="helper">{{placeholder('author')}}</small>
          </div>
          <div class="three fields" v-if="editing.publicationType != 'internetbron'">
            <div class="required field" v-if="editing.publicationType != 'tijdschriftartikel'">
              <label>Jaar van uitgave</label>
              <input type="text" v-model="editing.pubTimeSpan" :placeholder="placeholder('timespan')">
            </div>
            <div class="required field">
              <label>Plaats van uitgave</label>
              <input type="text" v-model="editing.pubLocation" :placeholder="placeholder('place')">
            </div>
            <div :class="editing.publicationType == 'boekbijdrage' ? 'field' : 'required field'" v-if="editing.publicationType == 'boekbijdrage' || editing.publicationType == 'tijdschriftartikel'">
              <label>Pagina's</label>
              <input type="text" v-model="editing.publicationPages" :placeholder="placeholder('pages')">
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
    {period: 'Bronstijd', min: -2000, max: -801},
    {period: 'IJzertijd', min: -800, max: -58},
    {period: 'Romeins', min: -57, max: 400},
    {period: 'vroegmiddeleeuws', min: 401, max: 900},
    {period: 'middeleeuws', min: 401, max: 1500},
    {period: 'volmiddeleeuws', min: 901, max: 1500},
    {period: 'laatmiddeleeuws', min: 1201, max: 1500},
    {period: 'postmiddeleeuws', min: 1501, max: 1900},
    {period: 'modern', min: 1901, max: new Date().getFullYear()},
    {period: 'Wereldoorlog I', min: 1914, max: 1918},
    {period: 'Wereldoorlog II', min: 1940, max: 1945}
  ]

  export default {
    props: ['cls', 'readonly'],
    data () {
      return {
        editing: null,
        fields: window.fields.classification,
        types: ['2.1', '2.2', '2.3'],
        tt : {
          title : false
        },
        placeholders : {
          title : {
            boek: "De titel van het boek.",
            boekbijdrage: "De titel van de boekbijdrage",
            tijdschriftartikel: "De titel van het artikel."
          },
          timespan: {
            boek: "Het jaartal boekpublicatie.",
            tijdschriftartikel: "Het jaartal van het artikel"
          },
          place : {
            boek: "Plaats uitgave van het boek.",
            boekbijdrage: "Plaats uitgave van het boek."
          },
          pages : {
            tijdschriftartikel: "Beginpagina - eindpagina",
            boekbijdrage: "Beginpagina - eindpagina"
          }
        }
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
      },
      isSourceRequired () {
        return this.cls.productionClassificationType === 'Publicatie van deze vondst'
      }
    },
    methods: {
      placeholder (element) {
        const pubType = this.editing.publicationType

        if (element == 'author' && pubType != 'internetbron') {
          return "Vul hier de namen van de auteur(s) van de publicatie in, in het formaat: voornaam naam. Vermeld maximaal twee namen, gescheiden door een &-teken. Bij meer dan twee auteurs , vermeld je enkel de eerste, gevolgd door: et al. Vb. C. Renfrew/C. Renfrew &amp; P. Bahn/C. Renfrew et al.";
        } else if (element == 'author' && pubType == 'internetbron') {
          return 'Vul hier de naam van de auteu van de webpagina of databankrecord (optioneel)'
        }

        return this.placeholders[element] && pubType && this.placeholders[element][pubType] ? this.placeholders[element][pubType] : ''
      },
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
        if (!empties) {
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
        if (! pub.publicationType) {
          return false;
        }

        switch (pub.publicationType) {
          case 'boek':
            return pub && pub.publicationTitle && pub.publicationType && pub.author && pub.pubTimeSpan && pub.pubLocation
          case 'boekbijdrage':
            return pub && pub.publicationTitle && pub.publicationType && pub.author && pub.pubTimeSpan && pub.pubLocation
          case 'tijdschriftartikel':
            return pub && pub.publicationTitle && pub.publicationType && pub.author && pub.pubTimeSpan && pub.publicationVolume && pub.publicationPages
          case 'internetbron':
            return pub && pub.publicationTitle && pub.publicationType && pub.author && pub.publicationContact
          default:
            return false;
        }
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
        //console.log(toPublication(this.editing));

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
    created: function () {
      window.addEventListener('keydown', this.keydown);
    },
    destroyed: function () {
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