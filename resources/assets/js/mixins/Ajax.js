export default {
  methods: {
    submit () {
      var data = this.formdata()
      console.log('Submitting', JSON.parse(JSON.stringify(data)))
      if (!this.submittable) {
        return console.warn('Tried to submit before submittable');
      }
      this.submitAction = this.submitAction || window.event.target.action || ''
      this.submitSuccess = this.submitSuccess || function () {console.warn('No success handler')}
      this.submitError = this.submitError || function () {console.warn('No error handler')}
      if (data.identifier) {
        this.$http.put(this.submitAction, data).then(this.submitSuccess, this.submitError)
      } else {
        this.$http.post(this.submitAction, data).then(this.submitSuccess, this.submitError)
      }
    }
  }
}
