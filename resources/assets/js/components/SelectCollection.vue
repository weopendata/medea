<template>
  <model-list-select
  style="width: 300px; display: inline-block;"
  :list="collections"
  option-value="identifier"
  option-text="title"
  v-model="selectedCollection"
  :placeholder="placeholder"
  @searchchange="searchCollection"
  >
  </model-list-select>
</template>

<script>
import ModelListSelect from 'vue-search-select/src/lib/ModelListSelect.vue';

export default {
  props: ['model', 'placeholder'],
  data () {
    return {
      collections: [],
      selectedCollection: '',
    }
  },
  methods: {
    searchCollection (value) {
      this.collections = [];
      axios.get('/api/collections?title=' + value)
        .then(response => {
          this.collections = response.data;
        })
    }
  },
  watch: {
    async selectedCollection (v) {
      var call = await axios.get('/collections/' + v);

      if (call.data) {
        this.$emit('select', call.data);
        this.collections = [];
      }
    }
  },
  components: {
    ModelListSelect
  }
}
</script>
