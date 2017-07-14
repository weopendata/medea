import Notifications from './mixins/Notifications'
import CollectionsList from './components/CollectionsList'

new window.Vue({
  el: 'body',
  mixins: [Notifications],
  components: {
    CollectionsList
  }
});
