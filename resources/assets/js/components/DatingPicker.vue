<template>
  <div class="fields">
    <div class="field" v-if="!show.period||!show.century||!show.ruler">
      <label>Datering</label>
      <button class="ui button" v-if="!show.period" @click.prevent="show.period=1">per cultuur</button>
      <button class="ui button" v-if="!show.century" @click.prevent="show.century=1">per eeuw</button>
      <button class="ui button" v-if="!show.ruler" @click.prevent="show.ruler=1">per heerser/authoriteit</button>
    </div>
    <div class="five wide field" v-show="show.period">
      <label>Datering op cultuur</label>
      <select class="ui dropdown" v-model="model.period">
        <option selected>onbekend</option>    
        <option>Bronstijd</option>
        <option>IJzertijd</option>
        <option>Romeins</option>
        <option>middeleeuws</option>
        <option>postmiddeleeuws</option>
        <option>modern</option>
        <option>Wereldoorlog I</option>
        <option>Wereldoorlog II</option>
      </select>
    </div>
    <div class="six wide field" v-show="show.century">
      <label>Datering per eeuw</label>
      <select class="ui fluid dropdown" v-model="model.century">
        <option selected>onbekend</option>    
        <option>20ste E. (1900/1999)</option>
        <option v-for="n in 9" v-text="(19-n)+'de E. (1'+(8-n)+'00/1'+(8-n)+'99)'"></option>
        <option v-for="n in 9" v-text="(10-n)+'de E. (0'+(9-n)+'00/0'+(9-n)+'99)'"></option>
        <option>1ste E. (0000/0099)</option>
        <option>1ste E. v.Chr. (-0099/0001)</option>
        <option v-for="n in 9" v-text="(n+2)+'de E. v.Chr. (-0'+(1+n)+'99/0'+(1+n)+'00)'"></option>
        <option v-for="n in 9" v-text="(n+11)+'de E. v.Chr. (-1'+n+'99/1'+n+'00)'"></option>
        <option>20ste E. v.Chr. (-1999/1900)</option>
      </select>
    </div>
    <div class="five wide field" v-show="show.ruler">
      <label>Datering per heerser</label>
      <select class="ui dropdown" v-model="model.ruler">
        <option selected>onbekend</option> 
        <option>Napoleon</option>
        <option>Filips de Schone</option>
        <option>Augustus</option>
        <option>Nero</option>
        <option>Franken</option>
        <option>Friezen</option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  props: ['model'],
  data () {
    return {
      show: {
        period: false,
        century: false,
        ruler: false
      }
    }
  }
}
</script>