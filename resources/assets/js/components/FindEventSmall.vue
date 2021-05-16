<template>
  <div class="card find-event-small__container" @click="goToDetail">
    <div class="find-event-small__img">
      <div :style="{ 'background-image': 'url(\'' + find.photograph + '\')'}" class="card-img-abs"
          style="max-height: 200px;" v-if="cardCover"></div>
      <div style="background:#fff" v-else></div>
    </div>
    <div class="find-event-small__content">
      <div class="find-event-small__title textual">
        {{ title }} ({{ label }})
      </div>
      <div>
        {{ city }}
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
      title() {
        return this.find._panTypologyInfo.mainCategory
      },
      label() {
        return this.find._panTypologyInfo.label
      },
      city () {
        return this.find._excavationInfo
          && this.find._excavationInfo.searchArea
          && this.find._excavationInfo.searchArea.location
          && this.find._excavationInfo.searchArea.location.address
          && this.find._excavationInfo.searchArea.location.address.locationAddressLocality
      }
    },
    methods: {
      goToDetail () {
        window.location.href = this.uri
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
