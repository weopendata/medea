import ls from 'local-storage'

export default {
  data () {
    return {
      notifications: ls('notifications') || [],
      notifTotal: ls('notifTotal') || 0,
      notifUnread: ls('notifUnread') || 0
    }
  },
  methods: {
    notifGo (n) {
      if (!typeof n === 'object' || !n.url) {
        return console.warn('notifGo could not decide where to go')
      }
      window.location.href = n.url
    },
    notifError ({data}) {
      console.warn(data)
    },
    notifSuccess ({data}) {
      console.log(data)
      this.notifications = data || ['jowkes']
      ls('notifications', data)
      ls('notifTotal', Math.floor((Math.random() * 10) + 10))
      ls('notifUnread', Math.floor((Math.random() * 10) + 1))
    },
    notifFetch () {
      console.log('fetching notifications', this.notifications)
      this.$http.get('/api/notifications')
      .then(this.notifSuccess, this.submitError)
      .catch(function () {
        this.submitting = false
      })
    },
    notifRead (id) {
      console.log('notif read', id)
      this.$http.post('/api/notifications/' + id, {read: 1}).then(this.notifSuccess, this.notifError)
    }
  },
  ready () {
    setTimeout(() => this.notifFetch(), 2000)
  }
}
