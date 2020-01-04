<template>
  <div>
    <pre>{{this.publication}}</pre>
    <div class="ui action input" v-if="publication.identifier" :class="{error:publication.error}">
      <input type="text" :value="cite(t)" readonly>
      <button type="button" class="ui basic icon button" @click="$parent.rmPublication(index)">
        <i class="unlinkify icon"></i>
      </button>
    </div>
    <div class="ui action input" v-else>
      <input type="text" v-model="publication.publicationTitle" placeholder="bibliografische referentie, URL, DOI, ..." readonly>
      <button type="button" class="ui button" @click="$parent.editPublication(publication, index)">Bewerken</button>
      <button type="button" class="ui basic icon button" @click="$parent.rmPublication(index)">
        <i class="unlinkify icon"></i>
      </button>
    </div>
  </div>
</template>

<script>
import {inert, fromPublication} from '../const.js'

import Publication from '../mixins/Publication.js'

export default {
  props: ['publication', 'index'],
  mixins: [Publication],
  computed: {
    t () {
      return fromPublication(this.publication);
    },
  }
}
</script>
