<template>
  <div class="map-controls">
    <div class="map-modal">
      <div class="field">
        <div class="ui right labeled fluid input">
          <input type="text" v-model="search" placeholder="zoeken">
          <button class="ui icon button label"><i class="search icon"></i></button>
        </div>
      </div>
      this is somethin
    </div>
    <a class="ui black button" @click.prevent="showmap=false">Terug naar lijst</a>
  </div>
</template>

<script>
export default {
	props: ['showmap']
}
</script>