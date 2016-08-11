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
import {EMPTY_CLS} from '../const.js'

export default {
  props: ['object'],
  data () {
    return {
      cls: EMPTY_CLS
    }
  },
  computed: {
    submittable () {
      return this.cls.productionClassificationType || this.cls.productionClassificationPeriod || this.cls.productionClassificationNation || this.cls.productionClassificationDescription
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
      this.$root.fetch()
      this.cls = EMPTY_CLS
    },
    submitError ({data}) {
      console.warn(data)
      this.$root.fetch()
    }
  },
  mixins: [Ajax],
  components: {
    AddClassificationForm
  }
}
</script>