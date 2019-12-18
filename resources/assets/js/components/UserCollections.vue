<template>
  <div class="ui form">
    <div class="collections">
      <div class="collection" v-for="collection in collections">
        <span class="remove" @click="remove(collection)" v-if="user.roles.includes('administrator')">&times;</span>
        <a :href="'/collections/' + collection.identifier">{{ collection.title }}</a>
      </div>
    </div>
    <div class="field collections__add" :class="{ error: errors.person }" v-if="user.roles.includes('administrator')">
      <label for="function">Collectie toewijzen aan dit profiel</label>
      <select-collection @select="assignCollection" placeholder="Zoek collectie"></select-collection>
      <div v-for="msg in errors.person" v-text="msg" class="input"></div>
    </div>
  </div>
</template>

<script>
import Ajax from '../mixins/Ajax'

import SelectCollection from './SelectCollection'

export default {
  props: ['collections', 'profile', 'errors'],
  data () {
    return {
      user: window.medeaUser || {},
    }
  },
  methods: {
    assignCollection (collection) {
      this.$emit('assignCollection', collection);
    },
    remove (collection) {
      this.$emit('removeCollection', collection);
    }
  },
  components: {
    SelectCollection
  },
  watch: {
    errors (v) {
      if (! v) {
        this.errors = {};
      }
    }
  }
}
</script>

<style lang="scss">
.collections {
  margin-bottom: 1em;
}
.collection {
  padding: 5px 10px;
}
.remove {
  margin-right: .5rem;
  cursor: pointer;
  padding: 5px 10px;
  &:hover {
    background: red;
    color: white;
  }
}
</style>
