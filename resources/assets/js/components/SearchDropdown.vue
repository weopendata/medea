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
      <template v-slot:clear :clear="clear">
        <div class="multiselect__clear" @mousedown.prevent="clear()">
          <div>X</div>
        </div>
      </template>
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
      },
      clear () {
        this.$emit('remove');
      }
    },
    components: {
      Multiselect
    }
  }
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style lang="scss">
.multiselect__clear {
  position: absolute;
  top: 10px;
  right: 30px;
  cursor: pointer;
}
</style>