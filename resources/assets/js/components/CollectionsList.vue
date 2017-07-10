<template>
  <div>
    <div class="ui very relaxed items">
      <div class="card" v-for="collection in collections">
        <div class="card-content">
          <div class="card-textual">
            <a : class="card-title">{{ collection.title }}</a>
            <div>
              {{ collection.description }}
            </div>
          </div>
          <!--
          <div class="card-bar">
            <a class="btn" :href="uri" v-if="user.validator&&find.validation == 'Klaar voor validatie'">
              Valideren
            </a>
            <a class="btn" :href="uri" v-if="find.validation == 'Gepubliceerd'">
              Bekijken
            </a>
            <a class="btn" href="#mapview" @click="mapFocus" v-if="hasLocation">
              <i class="marker icon"></i>
              Op de kaart
            </a>
            <a class="btn" :href="uriEdit" v-if="editable && (user.email==find.email)">
              <i class="pencil icon"></i>
              Bewerken
            </a>
            <button class="btn btn-icon pull-right" @click="rm" v-if="user.administrator||editable">
              <i class="trash icon"></i>
            </button>
            <a class="btn btn-icon pull-right" :href="uriEdit" v-if="user.administrator||editable">
              <i class="pencil icon"></i>
            </a>
          </div>-->
        </div>
      </div>
</template>
<script>
  import {inert, fromQuery} from '../const.js'

  export default {
    props: ['collections'],
    data () {
      return {}
    },
    computed: {
      currentPage () {
      }
    },
    methods: {
      to (q) {

      }
    },
    components: {}
  }
</script>