<template>
  <!-- Add publication -->
  <div class="ui dimmer modals page transition visible active">
    <div class="ui modal transition visible active">
      <div class="header">
        <h2>Publicatie bewerken</h2>
      </div>
      <div class="content">
        <div class="two fields">
          <div class="required field">
            <label @mouseover="tt.title = ! tt.title">Titel</label>
            <auto-input facet="title" :placeholder="placeholder('title')" :val="editing.publicationTitle" @update="updateEditingTitle"></auto-input>
          </div>
          <div class="required field">
            <label>Type publicatie</label>
            <select class="ui dropdown" v-model="editing.publicationType">
              <option>boek</option>
              <option>tijdschriftartikel</option>
              <option>boekbijdrage</option>
              <option>internetbron</option>
            </select>
          </div>
        </div>
        <div class="one field" v-if="editing.publicationType == 'tijdschriftartikel'">
          <div class="required field">
            <label>Titel tijdschrift</label>
            <auto-input facet="title" placeholder="De titel van het tijdschrift." :val="editing.parentTitle" @update="updateParentTitle"></auto-input>
          </div>
          <div class="required field">
            <label>Volume</label>
            <input type="text" v-model="editing.parentVolume" placeholder="Het volume of de jaargang van het tijdschrift.">
          </div>
        </div>
        <div class="one field" v-else-if="editing.publicationType == 'boekbijdrage'">
          <div class="required field">
            <label>Titel boek</label>
            <auto-input facet="title" placeholder="De titel van het boek." :val="editing.parentTitle" @update="updateParentTitle"></auto-input>
          </div>
          <div class="required field">
            <label>Redacteur</label>
            <auto-input facet="author" placeholder="" :val="editing.editor" @update="updateParentTitle"></auto-input>
            <small class="helper">Vul hier de namen in van de redacteur(s), op dezelfde manier als bij het auteurs veld.</small>
          </div>
        </div>
        <div class="one field" v-else-if="editing.publicationType == 'internetbron'">
          <div class="required field">
            <label>Titel website / databank</label>
            <auto-input facet="title" placeholder="De titel van de website of databank." :val="editing.parentTitle" @update="updateParentTitle"></auto-input>
          </div>
        </div>
        <div :class="editing.publicationType == 'internetbron' ? 'field' : 'required field'">
          <label>Namen van de auteurs</label>
          <auto-input facet="author" placeholder="" :val="editing.author" @update="updateAuthor"></auto-input>
          <small class="helper">{{placeholder('author')}}</small>
        </div>
        <div class="three fields" v-if="editing.publicationType != 'internetbron'">
          <div class="required field">
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
        <div class="required field" v-if="editing.publicationType == 'internetbron'">
          <label>URL</label>
          <input type="text" v-model="editing.publicationContact" placeholder="De volledige URL van de webpagina of databankrecord">
        </div>
        <div class="field" v-else>
          <label>URL van de publicatie</label>
          <input type="text" v-model="editing.publicationContact">
        </div>
        <br>
      </div>
      <div class="actions">
        <div :class="validPublication ? 'ui green button' : 'ui grey disabled button'" @click="savePublication">
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
</template>

<script>
  import AutoInput from './AutoInput.vue';

  import { inert } from '../const.js';

  export default {
    name: 'add-publication',
    props: ['publication', 'editingIndex'],
    mounted () {
      console.log("mounted the add publication modal");
      if (this.publication) {
        this.editing = this.publication;
      } else {
        this.editing = {
          title: '',
          publicationType: 'boek',
          parentTitle: '',
        }
      }
    },
    data () {
      return {
        editing: {},
        tt : {
          title : false
        },
        placeholders : {
          title : {
            boek: "De titel van het boek.",
            boekbijdrage: "De titel van de boekbijdrage",
            tijdschriftartikel: "De titel van het artikel.",
            internetbron: "Webpaginatitel / databankrecord nummer"
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
    methods: {
      updateEditingTitle (value) {
        this.editing.publicationTitle = value;
      },
      updateParentTitle (value) {
        this.editing.parentTitle = value;
      },
      updateAuthor (value) {
        this.editing.author = value;
      },
      validPublication () {
        if (this.editing.publicationType) {
          return this.isValidPublication(this.editing)
        }
      },
      placeholder (element) {
        const pubType = this.editing.publicationType

        if (element == 'author' && pubType != 'internetbron') {
          return "Vul hier de namen van de auteur(s) van de publicatie in, in het formaat: voornaam naam. Vermeld maximaal twee namen, gescheiden door een &-teken. Bij meer dan twee auteurs , vermeld je enkel de eerste, gevolgd door: et al. Vb. C. Renfrew/C. Renfrew & P. Bahn/C. Renfrew et al.";
        } else if (element == 'author' && pubType == 'internetbron') {
          return 'Vul hier de naam van de auteur van de webpagina of databankrecord (optioneel)'
        }

        return this.placeholders[element] && pubType && this.placeholders[element][pubType] ? this.placeholders[element][pubType] : ''
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
      closePublication () {
        this.$emit('close');
      },
      savePublication () {
        this.$emit('savePublication', {'publication': this.editing, 'index': this.editingIndex});
        //this.$set('cls.publication[editingIndex]', toPublication(this.editing))
        //this.closePublication()
      },
      rmPublication () {
        this.$emit('rmPublication', this.editingIndex);
      },
      keydown (evt) {
        evt = evt || window.event;
        if (evt.keyCode == 27) {
          this.closePublication()
        }
      }
    }
  }
</script>