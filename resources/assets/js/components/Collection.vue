<template>
  <div class="card">
    <div class="card-textual">
      <a class="card-title" :href="uri">{{ collection.title }}</a>
      <div>Persoon: <a :href="'/persons/' + person.id" v-for="person in collection.person">{{ person.firstName + ' ' + person.lastName }}</a></div>
      <div>Type: {{ collection.type }} </div>
      <div>Instelling: {{ collection.setting }}</div>
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
        return this.uri + '/delete'
      }
    },
    methods: {
      rm () {
        if (!confirm('Ben je zeker dat collectie #' + this.collection.identifier + ' verwijderd mag worden?')) {
          return
        }
        this.$http.delete(this.deleteUri).then(function () {
          console.log('removed', this.find.identifier)
          this.$root.fetch()
        });
      },
    }
  }
</script>