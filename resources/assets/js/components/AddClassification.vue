<template>
  <form class="ui form" @submit.prevent="submit" :action="submitAction" style="margin: 5em 0 5em;">
    <h2>Classificeren</h2>
    <div class="card card-center cls-card">
      <div class="card-textual">
        <add-classification-form :cls.sync="cls"></add-classification-form>
        <p>
          <button type="submit" class="ui button" :class="{green:submittable}" :disabled="!submittable">Toevoegen</button>
        </p>
      </div>
    </div>
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
      return this.cls.productionClassificationType || this.cls.productionClassificationPeriod || this.cls.productionClassificationRulerNation || this.cls.productionClassificationDescription
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