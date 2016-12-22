import ls from 'local-storage'

export default {
  data() {
    return {
      HelpText: ls('HelpText') || {

      }
    }
  },
  methods: {
    hideHelp(h) {
      this.HelpText[h] = true
      ls('HelpText', this.HelpText)
      this.HelpText = ls('HelpText')
    },
    showHelp(h) {
      this.HelpText[h] = false
      ls('HelpText', this.HelpText)
      this.HelpText = ls('HelpText')
    }
  }
}
