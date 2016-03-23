<template>
  <div>
    <div class="two fields">
      <div class="field">
        <label>Cultuur</label>
        <input type="text" v-model="cls.culture" placeholder="" id="street" list="cultures">
      </div>
      <div class="field">
        <label>Natie</label>
        <input type="text" v-model="cls.nation" placeholder="" list="nations">
      </div>
    </div>
    <div class="field">
      <label for="description">Referenties</label>
      <input type="text" v-model="ref" placeholder="Vul een URL in naar een publicatie" v-for="ref in cls.references" track-by="$index">
    </div>
    <div class="field">
      <label for="description">Opmerkingen</label>
      <textarea id="description" v-model="cls.description" :rows="descriptionLen"></textarea>
    </div>
    <datalist id="cultures">
      <option value="Bronstijd">
      <option value="Paleolithische cultuur">
      <option value="Cycladische beschaving">
      <option value="Clovis">
      <option value="Egyptisch">
      <option value="Hallstatt">
      <option value="Industrieel">
      <option value="Ijzertijd">
      <option value="Jastorf">
      <option value="Jemdet Nasr-periode">
      <option value="La Tène-periode">
      <option value="Mesolithisch">
      <option value="Mesopotamië">
      <option value="Miceense beschaving">
      <option value="Minoïsche beschaving">
      <option value="Milograd">
      <option value="Neolithisch">
      <option value="Schachttombe">
      <option value="Scyhen">
      <option value="Trojaburg">
      <option value="Urnenveld">
      <option value="Villanova">
      <option value="Steentijd">
      <option value="Grieks">
      <option value="Romaans">
    </datalist>
    <datalist id="nations">
      <option value="Belgisch">
      <option value="Frans">
    </datalist>
  </div>
</template>

<script>
export default {
  props: ['cls'],
  attached () {
    if (!this.cls) {
      this.$set('cls', {
        type: '',
        culture: '',
        nation: '',
        dating: '',
        references: [''],
        description: '',
      })
    } else if (!this.cls.references) {
      this.$set('cls.references', [''])
    }
  },
  watch: {
    'cls.references' (v) {
      var empties = 0;
      for (var i = 0; i < v.length; i++) {
        empties += v[i].length ? 0 : 1
      }
      console.log(empties, v, v.length, this.cls.references)
      if(!empties) {
        this.$nextTick(function () {
          this.cls.references.push('')
        })
      } else if (empties > 1) {
        this.$nextTick(function () {
          for (var i = 0; i < this.cls.references.length; i++) {
            if (!this.cls.references[i].length) {
              this.cls.references.splice(i, 1)
              break
            }
          }
        })
      }
    }
  }
}
</script>