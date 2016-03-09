<template>
  <div class="ui page grid">
    <div class="column">
  <div class="ui form">
    <slot>Form expected within ajax-form</slot>
  </div>
  </div></div>
</template>

<script>
import dropdown from 'semantic-ui-css/components/dropdown.min.js';
import transition from 'semantic-ui-css/components/transition.min.js';

export default {
  ready () {
    $('.ui.dropdown').dropdown()
  }
}
</script>