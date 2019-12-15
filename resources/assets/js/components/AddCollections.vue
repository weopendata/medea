<template>
  <div class="ui form">
    <div class="field collections__add">
      <label for="function">Koppelen aan een collectie</label>
      <select2 :options="options" @select="select" :clear-value="false" :value="formattedValue"></select2>
    </div>
  </div>
</template>
<script>
  import Select2 from './Select2.vue'

  export default {
    props: ['collection', 'placeholder'],
    data () {
      return {
        collections: window.collections || [],
        lastData: [],
        options: {
          width: '300px',
          placeholder: this.placeholder,
          allowClear: true,
          ajax: {
            url: '/api/collections',
            dataType: 'json',
            delay: 250,
            data (params) {
              return {
                title: params.term,
                page: params.page
              };
            },
            processResults: (data, params) => {
              this.lastData = data
              params.page = params.page || 1

              return {
                results: data.map(({identifier, title}) => ({
                  id: this.identifier === 0 ? "0" : identifier,
                  text: title
                })),
                pagination: {
                  more: (params.page * 30) < data.total_count
                }
              }
            },
            cache: true
          }
        }
      }
    },
    methods: {
      select (id) {
        const collection = this.lastData.find(p => p.identifier == id)
        if(collection){
          this.$emit('select', collection)
        }
        else{
          this.remove(this.collection)
        }
      },
      remove (collection) {
        this.$emit('remove', collection)
      }
    },
    computed: {
      formattedValue() {
        if (this.collection.identifier) {
          return {id: this.collection.identifier === 0 ? "0" : this.collection.identifier, text: this.collection.title}
        }
      }
    },
    components: {
      Select2
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