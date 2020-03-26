<template>
  <div>
    <multiselect
    :value="dropdownValue"
    :options="options"
    :searchable="true"
    @search-change="search"
    label="name"
    track-by="name"
    @input="input"
    :placeholder="placeholder"
    >
    </multiselect>
  </div>
</template>

<script>
  import Multiselect from 'vue-multiselect'

  export default {
    props: ['placeholder', 'options', 'value'],
    data () {
      return {
      }
    },
    computed: {
      dropdownValue: {
        get () {
          console.log(this.value);
          return this.value;
        }
      }
    },
    methods: {
      search (query) {
        this.$emit('search', query);
      },
      input (value) {
        this.$emit('input', value);
        //this.searchValue = '';
      }
    },
    components: {
      Multiselect
    },
    watch: {
      dropdownValue (v) {
        console.log(v, "dropdownvalue changed");
      }
    }
  }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>