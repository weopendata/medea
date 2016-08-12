<template>
  <div class="cls">
    <div class="cls-buttons">
      <button class="ui small icon button" :class="{green:me==='agree'}" @click.stop="agree">{{cls.agree}} <i class="thumbs up icon"></i></button>
      <button class="ui small icon button" :class="{red:me==='disagree'}" @click.stop="disagree">{{cls.disagree}} <i class="thumbs down icon"></i></button>
    </div>
    <div class="cls-content">
      <button class="ui small basic red icon button" @click.prevent.stop="rm" v-if="$root.user.administrator" style="float:right"><i class="trash icon"></i></button>
      <div v-if="cls.productionClassificationPeriod || cls.productionClassificationNation">
        <span class="cls-labeled" v-if="cls.productionClassificationPeriod">Periode <b>{{cls.productionClassificationPeriod}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationNation">Natie <b>{{cls.productionClassificationNation}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationType">Type <b>{{cls.productionClassificationType}}</b></span>
        <span class="cls-labeled" v-if="cls.startDate||cls.endDate">Datering <b>{{cls.startDate || '?'}} - {{cls.endDate || '?'}}</b></span>
      </div>
      <p class="cls-p" v-if="cls.productionClassificationDescription" v-text="cls.productionClassificationDescription"></p>
      <p v-if="cls.publication&&cls.publication.length">
        <i class="bookmark icon"></i>
        Referenties:
      </p>
      <div v-for="pub in cls.publication" v-if="pub.publicationTitle">
        {{pub.publicationTitle}}
      </div>
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
      this.$http.delete('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1)).then(this.$root.fetch)
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