<template>
  <div class="classification">
    <h4>{{cls.description || 'There should always be a description'}}</h4>
    <div>
      <div class="ui small icon buttons">
        <button class="ui button" :class="{green:me==='agree'}" @click.stop="agree">{{cls.agree}} <i class="thumbs up icon"></i></button>
        <button class="ui button" :class="{red:me==='disagree'}" @click.stop="disagree">{{cls.disagree}} <i class="thumbs down icon"></i></button>
      </div>
      <button class="ui small basic red button" @click.stop="rm" v-if="$root.user.isAdmin">Delete</button>
    </div>
  </div>
</template>

<script>
export default {
  props: ['cls', 'obj'],
  data () {
    return {
      me: this.cls.me || false
    }
  },
  methods: {
    agree () {
      this.cls[this.me]--
      this.me = this.me === 'agree' ? false : 'agree'
      this.cls[this.me]++
      this.$http({
        method: this.me ? 'POST' : 'DELETE',
        url: '/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/agree'
      })
    },
    disagree () {
      this.cls[this.me]--
      this.me = this.me === 'disagree' ? false : 'disagree'
      this.cls[this.me]++
      this.$http({
        method: this.me ? 'POST' : 'DELETE',
        url: '/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/disagree'
      })
    },
    rm () {
      this.$http.delete('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1))
    }
  },
  watch: {
    me (v) {
      this.cls.me = v
    }
  },
  components: {
  }
}
</script>

<style>
.classification {
  margin-bottom: 2rem;
}
</style>