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
  			culture: '',
  			nation: '',
  			dating: '',
  			description: '',
  		}
  	}
  },
  computed: {
  	submittable () {
  		return this.culture && this.description
  	},
  	submitAction () {
  		return '/objects/' + this.object.identifier + '/classifications'
  	},
  	descriptionLen () {
  		return 3 + Math.floor(this.cls.description.length / 50)
  	}
  },
  methods: {
  	formdata () {
  		return this.cls
  	},
  	submitSuccess ({data}) {
  		console.log(data)
  	},
  	submitError ({data}) {
  		console.warn(data)
  	}
  },
  mixins: [Ajax],
  components: {
    AddClassificationForm
  }
}
</script>