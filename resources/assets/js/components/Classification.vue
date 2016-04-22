<template>
  <div class="cls">
    <div class="cls-buttons">
      <button class="ui small icon button" :class="{green:me==='agree'}" @click.stop="agree">{{cls.agree}} <i class="thumbs up icon"></i></button>
      <button class="ui small icon button" :class="{red:me==='disagree'}" @click.stop="disagree">{{cls.disagree}} <i class="thumbs down icon"></i></button>
      <button class="ui small button" @click.stop="rm" v-if="$root.user.admin">Delete</button>
    </div>
    <div class="cls-content">
      <div v-if="cls.culture || cls.nation">
        <span class="cls-labeled" v-if="cls.culture">Cultuur <b>{{cls.culture}}</b></span>
        <span class="cls-labeled" v-if="cls.nation">Natie <b>{{cls.nation}}</b></span>
      </div>
      <p class="cls-p" v-if="cls.description" v-text="cls.description"></p>
    </div>
  </div>
</template>

<script>
export default {
  props: ['cls', 'obj'],
  data () {
    return {
      me: this.cls ? this.cls.me : false
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
.cls {
  margin-bottom: 1rem;
  border-bottom: 1px solid #ccc;
  padding: 1rem 0;
  overflow: auto;
}
.cls-content  {
  margin-left: 100px;
  font-size: 16px;
}
.cls-buttons {
  float:left;
  width: 70px;
}
.cls-buttons .button {
  background: none;
}
.cls-buttons .button>.icon{
  margin-left: 5px!important;
}
.cls-labeled {
  display: inline-block;
  margin-right: 1rem;
  margin-bottom: 1rem;
  padding: 2px 2px 2px 1rem;
  color: #666;
  line-height: 2rem;
  background-color: #ddd;
}
.cls-labeled b {
  display: inline-block;
  margin-left: 1rem;
  padding: 0 1rem;
  color: black;
  background-color: white;
}
.cls-p {
  padding: 5px 0;
  white-space: pre-wrap;
}
</style>