<template>
  <tr>
    <td>
      {{ apiKey.name }}
    </td>
    <td>
      {{ apiKey.api_key }}
    </td>
    <td>
      <button class="ui red button" @click.prevent="removeKey()"><i class="trash icon"></i></button>
    </td>
  </tr>
</template>

<script>
export default {
  name: 'ApiKeyRow',
  props: {
    apiKey: {
      type: Object
    }
  },
  methods: {
    removeKey () {
      this.$emit('remove', { id: this.apiKey.id })
    }
  }
}
</script>

<style scoped>

.api-key-row__container {
  display: flex;
  flex-direction: row;
}

</style>