import ls from 'local-storage'

export default {
  data () {
    return {
      notifications: ls('notifications') || [],
      notifUnread: ls('notifUnread') || 0,
      notifLast: ls('notifLast') || 0
    }
  },
  methods: {
    notifGo (n, index) {
      if (!typeof n === 'object' || !n.url) {
        return console.warn('notifGo could not decide where to go')
      }

      // Set notification as read
      this.notifRead(n, index)

      // Move to notification url
      window.location.href = n.url
    },
    notifError ({data}) {
      console.warn(data)
    },
    notifSuccess ({data}) {
      this.notifications = data.data
      this.notifUnread = data.unread
      this.notifLast = new Date().getTime()

      // Save for next page
      this.notifPersist()
    },
    notifFetch () {
      this.$http.get('/api/notifications')
      .then(this.notifSuccess, this.notifError)
    },
    notifRead (n, index) {
      if (n.read) {
        return
      }
      if (typeof index === 'number') {
        this.notifications[index].read = 1
      }
      if (this.notifUnread > 1) {
        this.notifUnread--
      }

      this.$http.post('/api/notifications/' + n.id, {read: 1})

      // Save for next page
      this.notifPersist()
    },
    notifPersist () {
      ls('notifications', this.notifications)
      ls('notifUnread', this.notifUnread)
      ls('notifLast', this.notifLast)
    }
  },
  mounted () {
    if (!window.medeaUser.isGuest) {
      setTimeout(() => this.notifFetch(), 3000)
    }
  }
}
