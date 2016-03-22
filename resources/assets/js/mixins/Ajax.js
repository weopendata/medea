export default {
  methods: {
    submit () {
      console.log('submit', this)
      if (!this.submittable) {
        return console.warn('Tried to submit before submittable');
      }
      this.submitAction = this.submitAction || ''
      this.submitSuccess = this.submitSuccess || function () {console.warn('No success handler')}
      this.submitError = this.submitError || function () {console.warn('No error handler')}
      this.$http.post(this.submitAction, this.formdata()).then(this.submitSuccess, this.submitError)
    }
  }
}
