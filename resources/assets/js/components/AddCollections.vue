<template>
  <div class="ui form">
    <div class="field collections__add">
      <label for="function">Koppelen aan een collectie</label>
      <search-dropdown placeholder="Selecteer een collectie" :options="options" :value="formattedCollection" @search="searchCollections" @input="select" />
    </div>
  </div>
</template>
<script>
  import SearchDropdown from './SearchDropdown'

  export default {
    props: ['collection', 'placeholder'],
    data () {
      return {
        options: [],
      }
    },
    methods: {
      select (collection) {
        //const collection = this.lastData.find(p => p.identifier == id)

        if (collection) {
          this.$emit('select', {identifier: collection.id, title: collection.name});
        }/* else {
          this.remove(this.collection)
        }*/
      },
      searchCollections (query) {
        axios.get('/api/collections?' + query)
        .then(response => {
          this.options = [];

          if (! response.data) {
            return;
          }

          this.options = response.data.map(result => {
            return {
              id: result.identifier,
              name:  result.title
            }
          });
        });
      },
      remove (collection) {
        this.$emit('remove', collection)
      }
    },
    computed: {
      formattedCollection() {
        if (this.collection && this.collection.identifier) {
          return {id: this.collection.identifier === 0 ? "0" : this.collection.identifier, name: this.collection.title}
        }
      }
    },
    watch: {
      collection (v) {
        console.log(v, "CHANGED");
      }
    },
    components: {
      SearchDropdown
    }
  }
</script>
<style lang="scss">
  .collections
  {
    margin-bottom: 1em;
  }
  .collection {
      padding: 5px 10px;
  }
  .remove {
      margin-right: .5rem;
      cursor:pointer;
      padding: 5px 10px;
      &:hover {
        background:red;
        color:white;
      }
  }
</style>