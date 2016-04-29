<template>
  <div class="ui very relaxed items">
    <find-event v-for="f in finds" :find="f" :user="user"></find-event>
    <div class="paging">
      <a v-if="paging.previous" :href="paging.previous.url||'#'" class="ui blue button">Vorige</a>
      <a v-if="paging.next" :href="paging.next.url||'#'" class="ui blue button">Volgende</a>
      <a v-if="paging.last" :href="paging.last.url||'#'" class="ui blue button">Laatste</a>
    </div>
    <div v-if="!finds.length" class="finds-empty">
      <h1>
        Geen resultaten
        <br><small>Er zijn geen vondsten die voldoen aan de criteria</small>
      </h1>
    </div>
    <div class="finds-cta">
      <p>
        <a href="" class="ui large button" v-if="!user.isGuest"><i class="ui alarm icon"></i> Zoekopdracht bewaren</a>
      </p>
    </div>
  </div>
</template>

<script>
import FindEvent from './FindEvent';

export default {
  props: ['user', 'finds', 'paging'],
  components: {
    FindEvent
  }
}
</script>