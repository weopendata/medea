<template>
  <div class="ui form">
    <div class="field collections__add">
      <label for="function">Deze vondst koppelen aan een collectie</label>
      <select-collection @select="select" placeholder="Zoek collectie"></select-collection>
    </div>
    <div class="collections">
      <div class="collection" v-if="collection.identifier">
        <span class="remove" @click="remove(collection)">&times;</span>
        <a :href="'/collections/' + collection.identifier">{{ collection.title }}</a>
      </div>
    </div>
  </div>
</template>
<script>
  import SelectCollection from './SelectCollection'

  export default {
    props: ['collection'],
    data () {
      return {
        collections: window.collections || [],
      }
    },
    methods: {
      select (collection) {
        this.$emit('select', collection)
      },
      remove (collection) {
        this.$emit('remove', collection)
      }
    },
    components: {
      SelectCollection
    }
  }
</script>
<style lang="sass">
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