<template>
  <div class="card">
    <div class="card-textual">
      <a class="card-title" :href="uri">{{ collection.title }}</a>
      <dl>
        <template v-if="collection.persons && collection.persons.length">
          <dt>Registratoren</dt>
          <dd>
            <template v-for="(index, person) in collection.persons">
              <span v-if="index">, </span>
              <a :href="'/persons/' + person.identifier">{{ person.firstName + ' ' + person.lastName }}</a>
            </template>
          </dd>
        </template>
        <template v-if="collection.collectionType">
          <dt>Type</dt>
          <dd>{{ collection.collectionType }}</dd>
        </template>
        <template v-if="collection.institutions">
          <dt>Instelling</dt>
          <dd>{{ collection.institutions }}</dd>
        </template>
        <template v-if="collection.objectNr">
          <dt>ObjectNr</dt>
          <dd>{{ collection.objectNr }}</dd>
        </template>
        <template v-if="collection.created_at">
          <dt>Aangemaakt op</dt>
          <dd>{{ collection.created_at | fromDate }}</dd>
        </template>
      </dl>
      <br>
      <div>
        {{ collection.description }}
      </div>
    </div>
    <div class="card-bar">
      <a class="btn" :href="filterUri">
        Bekijk vondsten
      </a>
      <button class="btn btn-icon pull-right" @click="rm" v-if="canEdit">
        <i class="trash icon"></i>
      </button>
      <a class="btn btn-icon pull-right" :href="editUri" v-if="canEdit">
        <i class="pencil icon"></i>
      </a>
    </div>
  </div>
</template>
<script>
  import ObjectFeatures from './ObjectFeatures';
  import { fromDate } from '../const'

  export default {
    props: ['collection'],
    computed: {
      uri () {
        return '/collections/' + this.collection.identifier
      },
      editUri () {
        return this.uri + '/edit'
      },
      deleteUri () {
        return this.uri
      },
      filterUri () {
        return '/finds?collection=' + this.collection.identifier
      },
      canEdit () {
        return window.medeaUser && window.medeaUser.administrator
      }
    },
    filters: {
      fromDate
    },
    methods: {
      rm () {
        if (!confirm('Ben je zeker dat collectie "' + this.collection.title + '" verwijderd mag worden?')) {
          return
        }
        this.$http.delete(this.deleteUri).then(function () {
          window.location.href = '/collections'
        });
      },
    }
  }
</script>