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
      <input-publication v-for="(index, pub) in cls.publication" :model="pub" :index="index"></input-publication>
      <div class="help-block">
        <button type="button" class="ui gray button" @click="addPublication">Toevoegen</button>
        <br>Vul verwijzingen in naar publicaties die je tot deze classificatie gebracht hebben.
        <br>
      </div>
    </div>
    <div class="field">
      <label for="description">Opmerkingen</label>
      <textarea-growing id="description" :model.sync="cls.productionClassificationDescription"></textarea-growing>
    </div>

    <div class="ui dimmer modals page transition visible active" v-if="editing">
      <div class="ui modal transition visible active">
        <div class="header">
          <h2>Publicatie bewerken</h2>
        </div>
        <div class="content">
          <div class="two fields">
            <div class="field">
              <label>Titel</label>
              <input type="text" v-model="editing.publicationTitle">
            </div>
            <div class="field">
              <label>Type</label>
              <select class="ui dropdown" v-model="editing.publicationType">
                <option>boek</option>
                <option>tijdschriftartikel</option>
                <option>boekbijdrage</option>
                <option>internetbron</option>
              </select>
            </div>
          </div>
          <div class="two fields">
            <div class="field">
              <label>Naam van de auteur</label>
              <input type="text" v-model="editing.author">
            </div>
            <div class="field">
              <label>Naam van de co-auteur</label>
              <input type="text" v-model="editing.coauthor">
            </div>
          </div>
          <div class="field">
            <label>Naam van de publisher</label>
            <input type="text" v-model="editing.publisher">
          </div>
          <div class="two fields">
            <div class="field">
              <label>Welke pagina van de publicatie?</label>
              <input type="text" v-model="editing.publicationPages">
            </div>
            <div class="field">
              <label>Hoe groot is de publicatie (aantal pagina's)</label>
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
          <div class="ui green button" @click="savePublication">
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

import { inert } from '../const.js'

const TYPE_AUTHOR = 'author'
const TYPE_COAUTHOR = 'coauthor'
const TYPE_PUBLISHER = 'publisher'

function fromPublication (p) {
  p = inert(p)
  var actors = ((p.publicationCreations || {}).publicationCreationActor || [])
  return Object.assign(p, {
    author: (actors.find(a => a.publicationCreationActorType === TYPE_AUTHOR) || {}).publicationCreationActorName,
    coauthor: (actors.find(a => a.publicationCreationActorType === TYPE_COAUTHOR) || {}).publicationCreationActorName,
    publisher: (actors.find(a => a.publicationCreationActorType === TYPE_PUBLISHER) || {}).publicationCreationActorName,
  })
}

function toPublication (p) {
  p = inert(p)
  return Object.assign(p, {
    publicationCreations:
      publicationCreationActor: [p.author && {
        publicationCreationActorName: p.author,
        publicationCreationActorType: TYPE_AUTHOR
      }, p.coauthor && {
        publicationCreationActorName: p.coauthor,
        publicationCreationActorType: TYPE_COAUTHOR
      }, p.publisher && {
        publicationCreationActorName: p.publisher,
        publicationCreationActorType: TYPE_PUBLISHER
      }].filter(Boolean)
    }
  })
}

export default {
  props: ['cls'],
  data () {
    return {
      editing: null,
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
    },
    editPublication (pub, index) {
      this.editing = fromPublication(pub)
      this.editingIndex = index
    },
    savePublication () {
      this.$set('cls.publication[editingIndex]', toPublication(this.editing))
      this.editing = null
      this.editingIndex = -1
    },
    addPublication () {
      const pub = { }
      this.cls.publication.push(pub)
      this.editPublication(pub, this.cls.publication.length - 1)
    },
    rmPublication () {
      this.cls.publication.splice(this.editingIndex, 1)
      this.editing = null
      this.editingIndex = -1
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
    InputPublication,
    TextareaGrowing
  }
}
</script>