<template>
  <div class="ui form">
    <div class="field persons__add">
      <label for="function">Koppelen aan een persoon</label>
      <search-dropdown placeholder="Selecteer een persoon" :options="options" :value="formattedPerson" @search="searchPersons" @input="select" @remove="remove" />
    </div>
  </div>
</template>
<script>
  import SearchDropdown from './SearchDropdown'

  export default {
    props: ['person', 'placeholder'],
    data () {
      return {
        options: [],
      }
    },
    methods: {
      select (person) {
        if (person) {
          this.$emit('select', {identifier: person.id, name: person.name});
        }
      },
       searchPersons (query) {
        axios.get('/api/users?name=' + name)
        .then(response => {
          this.options = [];

          if (! response.data) {
            return;
          }

          this.options = response.data.map(result => {
            return {
              id: result.identifier,
              name:  result.firstName + ' ' + result.lastName
            }
          });
        });
      },
      remove () {
        this.$emit('remove')
      }
    },
    computed: {
      formattedPerson() {
        if (this.person) {
          var name = this.person.name;

          if (! name && this.person.firstName) {
            name = this.person.firstName + ' ' + this.person.lastName;
          }

          return {
            id: this.person.identifier === 0 ? "0" : this.person.identifier,
            name: name
          }
        }
      }
    },
    components: {
      SearchDropdown
    }
  }
</script>
<style lang="scss">
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