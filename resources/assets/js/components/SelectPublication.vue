<template>
    <model-list-select
    style="width: 300px; display: inline-block;"
    :list="publications"
    option-value="id"
    option-text="title"
    v-model="selectedPublication"
    placeholder="Zoeken in bestaande publicaties"
    @searchchange="searchPublication"
    >
</model-list-select>
</template>

<script>

import ModelListSelect from 'vue-search-select/src/lib/ModelListSelect.vue';

export default {
  data () {
    return {
      searchText: '',
      publications: [],
      selectedPublication: '',
    }
  },
  methods: {
    searchPublication (value) {
      axios.get('/api/publications?query=' + value)
        .then(response => {
          this.publications = [];

          if (! response.data) {
            return;
          }

          this.publications = response.data.map(({ identifier, title }) => ({ id: identifier, title: title }));
        })
    },
    addPublication (id) {
      this.$emit('publicationSelect', id);
      this.searchText = '';
      this.publications = [];
      /*this.$parent.cls.publication.push({
        identifier: id,
        publicationTitle: ''
      })*/
    }
  },
  components: {
    ModelListSelect
  },
  watch: {
    selectedPublication (id) {
      this.addPublication(id);
    }
  }
}
</script>
