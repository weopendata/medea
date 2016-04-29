<template>
  <form class="ui form" @submit.prevent="submit" :action="submitAction">
    <h2>Classificeren</h2>
    <add-classification-form :cls.sync="cls"></add-classification-form>
    <p>
      <button type="submit" class="ui button" :class="{green:submittable}" :disabled="!submittable">Toevoegen</button>
    </p>
  </form>
</template>

<script>
import AddClassificationForm from './AddClassificationForm'
import Ajax from '../mixins/Ajax'

export default {
  props: ['object'],
  data () {
    return {
      cls: {
        type: '',
        period: '',
        nation: '',
        startDate: '',
        endDate: '',
        publication: [{publicationTitle: ''}],
        description: '',
      }
    }
  },
  computed: {
    submittable () {
      return this.cls.type || this.cls.period || this.cls.nation || this.cls.description
    },
    submitAction () {
      return '/objects/' + this.object.identifier + '/classifications'
    }
  },
  methods: {
    formdata () {
      return this.cls
    },
    submitSuccess ({data}) {
      window.location.reload()
    },
    submitError ({data}) {
      console.warn(data)
      window.location.reload()
    }
  },
  mixins: [Ajax],
  components: {
    AddClassificationForm
  }
}
</script>