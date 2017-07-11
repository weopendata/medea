<template>
  <div class="card">
    <div class="card-textual">
      <a class="card-title" :href="uri">{{ collection.title }}</a>
      <div v-if="collection.persons && collection.persons.length">Registratoren:
        <template v-for="(index, person) in collection.persons">
          <span v-if="index">, </span>
          <a :href="'/persons/' + person.identifier">{{ person.firstName + ' ' + person.lastName }}</a>
        </template>
      </div>
      <dl v-if="collection.collectionType">
        <dt>Type</dt>
        <dd>{{ collection.collectionType }}</dd>
      </dl>
      <dl v-if="collection.institution">
        <dt>Instelling</dt>
        <dd>{{ collection.institution }}</dd>
      </dl>
      <br><br>
      <div>
        {{ collection.description }}
      </div>
    </div>
    <div class="card-bar">
      <a class="btn" :href="uri">
        Bekijken
      </a>
      <button class="btn btn-icon pull-right" @click="rm">
        <i class="trash icon"></i>
      </button>
      <a class="btn btn-icon pull-right" :href="editUri">
        <i class="pencil icon"></i>
      </a>
    </div>
  </div>
</template>
<script>
  import ObjectFeatures from './ObjectFeatures';

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
      }
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