<template>
<div class="card card-center fe-card">
    <div class="card-textual">
      <p>
        Deze classificatie werd opgesteld door vondstexpert
      </p>
      <div v-if="cls.productionClassificationPeriod || cls.productionClassificationNation">
        <span class="cls-labeled" v-if="cls.productionClassificationPeriod">Periode <b>{{cls.productionClassificationPeriod}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationNation">Natie <b>{{cls.productionClassificationNation}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationType">Type <b>{{cls.productionClassificationType}}</b></span>
        <span class="cls-labeled" v-if="cls.startDate||cls.endDate">Datering <b>{{cls.startDate || '?'}} - {{cls.endDate || '?'}}</b></span>
      </div>
      <p class="cls-p" v-if="cls.productionClassificationDescription" v-text="cls.productionClassificationDescription"></p>
      <p v-if="singlePub">
        <i class="bookmark icon"></i>
        Referentie:
        <a :href="singlePub.href" v-if="singlePub.href" v-text="singlePub.href"></a>
        <span v-else v-text="singlePub.text"></span>@@
      </p>
      <div v-if="multiPub">
        <p v-if="cls.publication&&cls.publication.length">
          <i class="bookmark icon"></i>
          Referenties:
        </p>
        <div v-for="pub in pubs">@
          <a :href="pub.href" v-if="pub.href" v-text="pub.href"></a>
          <span v-else v-text="pub.text"></span>
        </div>
      </div>
    </div>
    <div class="card-bar">
      <button class="btn" :class="{green:me==='agree'}" @click.stop="agree">{{cls.agree}} <i class="thumbs up icon"></i></button>
      <button class="btn" :class="{red:me==='disagree'}" @click.stop="disagree">{{cls.disagree}} <i class="thumbs down icon"></i></button>

      <button class="btn" @click.prevent.stop="rm" v-if="$root.user.administrator" style="float:right"><i class="trash icon"></i></button>

    </div><!--v-if-->
  </div>
</template>

<script>
function urlify (u) {
  if (u && u.slice(0, 4) === 'http') {
    return {
      href: u
    }
  }
  return {
    text: u
  }
}
export default {
  props: ['cls', 'obj'],
  data () {
    return {
      me: this.cls ? this.cls.me : false
    }
  },
  computed: {
    multiPub () {
      return this.cls.publication && this.cls.publication.length > 1
    },
    singlePub () {
      return this.pubs && this.pubs.length === 1 && this.pubs[0]
    },
    pubs () {
      return this.cls.publication && this.cls.publication.map(p => urlify(p.publicationTitle))
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