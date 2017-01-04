<template>
  <select2 :options="options" @select="addPublication"></select2>
</template>

<script>
import Select2 from './Select2.vue'

export default {
  props: ['model'],
  data () {
    return {
      options: {
        width: '300px',
        placeholder: 'Zoeken in bestaande publicaties',
        ajax: {
          url: '/api/publications',
          dataType: 'json',
          delay: 250,
          data: function (params) {
            return {
              query: params.term,
              page: params.page
            };
          },
          processResults: function (data, params) {
            params.page = params.page || 1

            return {
              results: data.map(({ identifier, title }) => ({ id: identifier, text: title })),
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
    addPublication (id) {
      this.$parent.cls.publication.push({
        identifier: id,
        publicationTitle: ''
      })
    }
  },
  components: {
    Select2
  }
}
</script>
