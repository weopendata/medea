export default {
  data () {
    return {
      submitting: false
    }
  },
  methods: {
    submit () {
      var data = this.formdata()

      if (!this.submittable) {
        return console.warn('Tried to submit before submittable');
      }
      this.submitting = true
      this.submitAction = this.submitAction || window.event.target.action || ''
      this.submitSuccess = this.submitSuccess || function () {console.warn('No success handler')}
      this.submitError = this.submitError || function () {console.warn('No error handler')}
      if (this.submitTrack) {
        this.submitTrack(data)
      }

      var request = data.identifier ? this.$http.put(this.submitAction, data) : this.$http.post(this.submitAction, data)

      request
        .then(this.submitSuccess, this.submitError)
        .then(function () {
          this.submitting = false
        })
        .catch(function () {
          this.submitting = false
        })
    }
  }
}
