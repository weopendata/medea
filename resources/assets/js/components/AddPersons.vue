<template>
  <div class="ui form">
    <div class="field persons__add">
      <label for="function">Koppelen aan een persoon</label>
      <select2 :options="options" @select="select"></select2>
    </div>
    <div class="persons">
      <div class="person" v-if="person.firstName">
        <span class="remove" @click="remove(person)">&times;</span>
        <a :href="'/persons/' + person.identifier">{{ person.firstName }} {{ person.lastName }}</a>
      </div>
    </div>
  </div>
</template>
<script>
  import Select2 from './Select2.vue'

  export default {
    props: ['person', 'placeholder'],
    data () {
      return {
        lastData: [],
        options: {
          width: '300px',
          placeholder: this.placeholder,
          ajax: {
            url: '/api/users',
            dataType: 'json',
            delay: 250,
            data (params) {
              return {
                name: params.term,
                page: params.page
              };
            },
            processResults: (data, params) => {
              this.lastData = data
              params.page = params.page || 1

              return {
                results: data.map(({identifier, firstName, lastName}) => ({id: identifier, text: firstName + ' ' + lastName})),
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
        const person = this.lastData.find(p => p.identifier == id)
        person && this.$emit('select', person)
      },
      remove (person) {
        this.$emit('remove', person)
      }
    },
    components: {
      Select2
    }
  }
</script>
<style lang="sass">
  .persons {
    margin-bottom: 1em; }
    .person {
      padding: 5px 10px;
  }
    .remove {
      margin-right: .5rem;
      cursor:pointer;
      padding: 5px 10px;
      &:hover {
        background:red;
        color:white;
  } }
</style>