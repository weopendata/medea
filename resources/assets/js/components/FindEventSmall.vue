<template>
  <div class="card find-event-small__container" @click="goToDetail">
    <div class="find-event-small__img">
      <div :style="{ 'background-image': 'url(\'' + find.photograph + '\')'}" class="card-img-abs"
          style="max-height: 200px;" v-if="cardCover"></div>
      <div style="background:#fff" v-else></div>
    </div>
    <div class="find-event-small__content">
      <div class="find-event-small__title textual">
        {{ find._panTypologyInfo.label }} ({{ find._panTypologyInfo.mainCategory }})
      </div>
      <br/>
      <div style="font-size: 12px;">
        {{ find.excavationTitle }}
      </div>
    </div>
  </div>
</template>

<script>
  import {fromDate} from "../const";
  import FindHelper from "../mixins/FindHelper";

  export default {
    name: "FindEventSmall",
    props: ['find'],
    computed: {
      cardCover() {
        return this.find.photograph;
      },
      uri() {
        return '/finds/' + this.find.identifier
      },
      city () {
        return this.find.excavationAddressLocality
      }
    },
    methods: {
      goToDetail () {
        window.open(this.uri, '_blank')
      }
    },
    filters: {
      fromDate
    },
    mixins: [FindHelper]
  }
</script>

<style scoped>
  .find-event-small__container {
    display: flex;
    flex-direction: column;
    width: 200px;
    min-width: 200px;
    margin-left: 1rem;
    margin-right: 1rem;
    cursor: pointer;
  }

  .find-event-small__img {
    width: 200px;
    background-size: cover;
    height: 200px;
    max-height: 200px;
  }

  .find-event-small__title {
    font-size: 14px;
    font-weight: 600;
  }

  .find-event-small__content {
    padding: 0.5rem;
  }
</style>
