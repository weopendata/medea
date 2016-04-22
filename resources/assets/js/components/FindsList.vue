<template>
  <div class="ui very relaxed items">
    <find-event v-for="f in finds" :find="f" :user="user"></find-event>
    <div class="paging">
      <a :href="paging.previous&&paging.previous.url||'#'" class="ui button" :class="{blue:paging.previous}">Vorige</a>
      <a :href="paging.next&&paging.next.url||'#'" class="ui button" :class="{blue:paging.next}">Volgende</a>
      <a :href="paging.last&&paging.last.url||'#'" class="ui button" :class="{blue:paging.last}">Laatste</a>
    </div>
    <div v-if="!finds.length" class="finds-empty">
      <h1>
        Geen resultaten
        <br><small>Er zijn geen vondsten die voldoen aan de criteria</small>
      </h1>
    </div>
    <div class="finds-cta">
      <p>
        <a href="" class="ui large button" v-if="!user.isGuest"><i class="ui alarm icon"></i> Notificaties voor deze zoekopdracht</a>
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