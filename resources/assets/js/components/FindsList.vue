<template>
  <div class="ui very relaxed items">
    <find-event v-for="findEvent in finds" :find="findEvent" :user="user"></find-event>
  </div>
</template>

<script>
import FindEvent from './FindEvent';

export default {
  props: ['user', 'finds'],
  components: {
    FindEvent
  }
}
</script>