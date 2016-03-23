<template>
  <div class="classification">
    <h4>{{cls.description || 'There should always be a description'}}</h4>
    <div>
      <div class="ui small icon buttons">
        <button class="ui button" :class="{green:voted==='agree'}" @click.stop="agree">0 <i class="thumbs up icon"></i></button>
        <button class="ui button" :class="{red:voted==='disagree'}" @click.stop="disagree">2 <i class="thumbs down icon"></i></button>
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
      voted: this.cls.voted || false
    }
  },
  methods: {
    agree () {
      this.voted = this.voted === 'agree' ? false : 'agree'
      this.$http.post('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/agree')
    },
    disagree () {
      this.voted = this.voted === 'disagree' ? false : 'disagree'
      this.$http.post('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/disagree')
    },
    rm () {
      this.$http.delete('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1))
    }
  },
  watch: {
    voted (v) {
      this.cls.voted = v
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