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
      <!-- Source -->
      <div class="field field-publications" v-if="isSourceRequired">
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
      <!-- Find place -->
      <div :class="isSourceRequired ? 'field' : 'required field'">
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
          <div class="help-block">Onder welke heerser (bijv. Nero) of welk volk (bijv. Eburonen) is de munt geslagen?</div>
        </div>
      </div>
      <!-- Date -->
      <div class="two fields" @change="limitPeriod">
        <div class="field" :class="{error: validRange, required: isTypology}">
          <label>Datering van</label>
          <input-date v-model="cls.startDate"></input-date>
          <div class="help-block">{{ isSourceRequired ? 'Vul hier de startdatum in van de datering die de auteur aan deze vondst toewijst.' : isTypology ? 'Vul hier de startdatum in van dit type, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\').' : 'Vul hier de startdatum van de gelijkaardige vondst in, zoals aangegeven in de literatuur (waar je naar verwijst in het veld \'Bron\') (optioneel).' }}</div>
        </div>
        <div class="field" :class="{error: validRange, required: isTypology}">
          <label>Datering tot</label>
          <input-date v-model="cls.endDate"></input-date>
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
          <select-publication :model="pub"></select-publication>
        </div>
        <br>
        <button type="button" class="ui gray button" @click="addPublication">Bron toevoegen</button>
      </div>
      <!-- Remark -->
      <div class="field">
        <label for="description">Opmerking</label>
        <textarea-growing id="description" v-model="cls.productionClassificationDescription"></textarea-growing>
        <div class="help-block">
          Voeg hier eventueel een opmerking toe aan je classificatie (optioneel).
        </div>
      </div>
    </div>

    <add-publication
    v-if="editing"
    @rmPublication="rmPublication"
    @savePublication="savePublication"
    :publication="editing"
    :editingIndex="editingIndex"
    @close="closePublicationModal"
    >
    </add-publication>

    <datalist id="types">
      <option v-for="opt in fields.type" :value="opt"></option>
    </datalist>
    <datalist id="nations">
      <option value="Napoleon"/>
      <option value="Caesar"/>
      <option value="Cleopatra"/>
    </datalist>
  </div>
</template>

<script>
  import TextareaGrowing from './TextareaGrowing';
  import InputDate from './InputDate';
  import InputPublication from './InputPublication.vue';
  import SelectPublication from './SelectPublication.vue';
  import AddPublication from './AddPublication.vue';

  import { fromPublication, toPublication } from '../const.js'

  const dateRanges = [
    {period: 'Bronstijd', min: -2000, max: -801},
    {period: 'IJzertijd', min: -800, max: -58},
    {period: 'Romeins', min: -57, max: 400},
    /*{period: 'vroegmiddeleeuws', min: 401, max: 900},*/
    {period: 'middeleeuws', min: 401, max: 1500},
    /*{period: 'volmiddeleeuws', min: 901, max: 1500},
    {period: 'laatmiddeleeuws', min: 1201, max: 1500},*/
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
      isSourceRequired () {
        return this.cls.productionClassificationType === 'Publicatie van deze vondst'
      }
    },
    methods: {
      closePublicationModal () {
        if (!this.isValidPublication(this.cls.publication[this.editingIndex])) {
          this.rmPublication(this.editingIndex)
        }

        this.editing = null
        this.editingIndex = -1
      },
      isValidPublication (pub) {
        if (! pub.publicationType) {
          return false;
        }

        switch (pub.publicationType) {
          case 'boek':
          return pub && pub.publicationTitle && pub.publicationType && pub.author && pub.pubTimeSpan && pub.pubLocation
          case 'boekbijdrage':
          return pub && pub.publicationTitle && pub.publicationType && pub.author && pub.pubTimeSpan && pub.pubLocation && pub.editor && pub.parentTitle
          case 'tijdschriftartikel':
          return pub && pub.publicationTitle && pub.publicationType && pub.author && pub.pubTimeSpan && pub.parentVolume && pub.parentTitle && pub.publicationPages
          case 'internetbron':
          return pub && pub.publicationTitle && pub.publicationType && pub.publicationContact && pub.parentTitle
          default:
          return false;
        }
      },
      editPublication (pub, index) {
        this.editing = fromPublication(pub)
        this.editingIndex = index;
      },
      addPublication (pub) {
        /*pub = pub || {}
        this.cls.publication.push(pub)
        this.editPublication(pub, this.cls.publication.length - 1)*/
        this.editing = fromPublication(pub);
        this.editingIndex = null;
      },
      savePublication (pub) {
        this.cls.publication.push(pub);
        console.log(pub);
        console.log(this.cls.publication);

        this.editing = null;
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
      setMainType (type) {
        this.cls.productionClassificationType = type
      },
      limitDateRange () {
        const period = this.cls.productionClassificationCulturePeople
        const range = dateRanges.find(r => r.period === period)

        if (range) {
          return;
        }

        if (range.min && (!this.cls.startDate || this.cls.startDate < range.min || this.cls.startDate >= range.max)) {
          this.cls.startDate = range.min
        }
        if (range.max && (!this.cls.endDate || this.cls.endDate > range.max || this.cls.endDate <= range.min)) {
          this.cls.endDate = range.max
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
    },
    mounted () {
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
      TextareaGrowing,
      AddPublication,
    }
  }
</script>