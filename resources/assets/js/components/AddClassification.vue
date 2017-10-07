<template>
  <form class="ui form" @submit.prevent="submit" :action="submitAction" style="margin: 5em 0 5em;">
    <h2>Classificeren</h2>
    <div class="card card-center cls-card">
      <div class="card-textual">
        <add-classification-form :cls.sync="cls"></add-classification-form>
        <p v-if="cls && cls.productionClassificationType">
          <br>
          <button type="submit" class="ui button" :class="{green:submittable}" :disabled="!submittable">Toevoegen</button>
          <button type="submit" class="ui button" @click="cancel">Annuleren</button>
        </p>
      </div>
    </div>
  </form>
</template>

<script>
import AddClassificationForm from './AddClassificationForm'
import Ajax from '../mixins/Ajax'
import { emptyClassification } from '../const.js'

export default {
  props: ['object'],
  data () {
    return {
      cls: emptyClassification()
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
    cancel () {
      this.cls = emptyClassification()
    },
    formdata () {
      return this.cls
    },
    submitSuccess ({data}) {
      this.$root.fetch()
      this.cls = emptyClassification()
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