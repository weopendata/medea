<template>
  <select2 :options="options" @select="select"></select2>
</template>

<script>
import Select2 from './Select2.vue'

export default {
  props: ['model', 'placeholder'],
  data () {
    return {
      lastData: [],
      options: {
        width: '300px',
        placeholder: this.placeholder,
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
              results: data.map(({ identifier, title }) => ({ id: identifier || 1, text: title })),
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
      const collection = this.lastData.find(p => p.identifier.toString() === id)
      collection && this.$emit('select', collection)
    }
  },
  components: {
    Select2
  }
}
</script>
