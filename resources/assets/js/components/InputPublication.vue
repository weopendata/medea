<template>
  <div class="ui action input" v-if="model.identifier">
    <input type="text" :value="model.publicationTitle || ('Publicatie ' + model.identifier)" readonly>
    <button type="button" class="ui basic icon button" @click="$parent.rmPublication(index)">
      <i class="unlinkify icon"></i>
    </button>
  </div>
  <div class="ui action input" v-else>
    <input type="text" v-model="model.publicationTitle" placeholder="bibliografische referentie, URL, DOI, ..." readonly>
    <button type="button" class="ui button" @click="$parent.editPublication(model, index)">Bewerken</button>
    <button type="button" class="ui basic icon button" @click="$parent.rmPublication(index)">
      <i class="unlinkify icon"></i>
    </button>
  </div>
</template>

<script>
export default {
  props: ['model', 'index'],
  computed: {
    title () {
      return this.model.publicationTitle
    }
  }
}
</script>
