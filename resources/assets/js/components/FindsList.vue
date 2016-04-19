<template>
  <div class="ui very relaxed items">
    <find-event v-for="f in finds | filterBy relevant" :find="f" :user="user"></find-event>
    <div v-if="finds.length>20">
      <button class="ui button">Vorige</button>
      <button class="ui blue button">Volgende</button>
    </div>
  </div>
</template>

<script>
import FindEvent from './FindEvent';

export default {
  props: ['user', 'finds'],
  methods: {
    relevant (find) {
      console.log('rel')
      return find.object.objectValidationStatus == 'gevalideerd'
      || (this.user.isValidator && find.object.objectValidationStatus == 'in bewerking')
    }
  },
  components: {
    FindEvent
  }
}
</script>