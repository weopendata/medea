<template>
  <div class="card card-center cls-card">
    <div class="card-textual">
      <p v-if="cls.productionClassificationPeriod || cls.productionClassificationRulerNation || cls.productionClassificationType || cls.startDate || cls.endDate">
        <span class="cls-labeled" v-if="cls.productionClassificationPeriod">Periode <b>{{cls.productionClassificationPeriod}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationRulerNation">Natie <b>{{cls.productionClassificationRulerNation}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationType">Type <b>{{cls.productionClassificationType}}</b></span>
        <span class="cls-labeled" v-if="cls.startDate||cls.endDate">Datering <b>{{vC(cls.startDate) || '?'}} - {{vC(cls.endDate, cls.startDate) || '?'}}</b></span>
      </p>
      <p class="cls-p" v-if="cls.productionClassificationDescription" v-text="cls.productionClassificationDescription"></p>
      <p v-if="singlePub">
        Referentie:
        <a :href="singlePub.href" v-if="singlePub.href" v-text="singlePub.href"></a>
        <span v-else v-text="singlePub.text"></span>@
      </p>
      <p v-if="multiPub">
        Referenties:
        <span v-for="pub in pubs">
          <br>
          <a :href="pub.href" v-if="pub.href" v-text="pub.href"></a>
          <span v-else v-text="pub.text"></span>
        </span>
      </p>
      <p v-if="cls.agree">
        <i class="thumbs up icon"></i> {{cls.agree}} vondstexpert{{cls.agree===1?' is':'s zijn'}} het eens met deze classificatie
      </p>
      <p v-if="cls.disagree">
        <i class="thumbs down icon"></i> {{cls.disagree}} vondstexpert{{cls.disagree===1?' is':'s zijn'}} het oneens met deze classificatie
      </p>
    </div>
    <div class="card-bar card-bar-border">
      <span v-if="user.vondstexpert">
        <button class="btn" :class="{green:cls.me==='agree'}" @click.stop="agree"><i class="thumbs up icon"></i></button>
        <button class="btn" :class="{red:cls.me==='disagree'}" @click.stop="disagree"><i class="thumbs down icon"></i></button>
      </span>{{cls.me}}
      <span class="cls-creator" v-if="creator||cls.created_at">
        Opgesteld
        <span v-if="creator">door {{creator}}</span>
        <time v-if="cls.created_at" :title="cls.created_at">op {{cls.created_at | fromDate}}</time>
        <time v-if="cls.updated_at!==cls.created_at" :title="cls.updated_at" style="margin-left:1em">update op {{cls.updated_at | fromDate}}</time>
      </span>
      <div style="float:right">
        <button class="btn" @click.prevent.stop="rm" v-if="$root.user.administrator"><i class="trash icon"></i></button>
      </div>
    </div>
  </div>
</template>

<script>
import {fromDate, urlify} from '../const.js'

export default {
  props: ['cls', 'obj'],
  computed: {
    creator () {
      return this.cls && this.cls.person && this.cls.person.name
    },
    multiPub () {
      return this.pubs && this.pubs.length > 1
    },
    singlePub () {
      return this.pubs && this.pubs.length === 1 && this.pubs[0]
    },
    pubs () {
      return this.cls.publication && this.cls.publication.map(p => urlify(p.publicationTitle)).filter(Boolean)
    }
  },
  methods: {
    vC (y1, y2) {
      if (y1 < 0 || y2 < 0) {
        return y1 < 0 ? -y1 + ' v.C.' : y1 + ' n.C.'
      }
      return y1
    },
    agree () {
      this.cls[this.cls.me]--
      this.$set('cls.me', this.cls.me === 'agree' ? null : 'agree')
      this.cls[this.cls.me]++
      this.$http({
        method: this.cls.me ? 'POST' : 'DELETE',
        url: '/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/agree'
      })
    },
    disagree () {
      this.cls[this.cls.me]--
      this.$set('cls.me', this.cls.me === 'disagree' ? null : 'disagree')
      this.cls[this.cls.me]++
      this.$http({
        method: this.cls.me ? 'POST' : 'DELETE',
        url: '/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/disagree'
      })
    },
    rm () {
      this.$http.delete('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1)).then(this.$root.fetch)
    }
  },
  filters: {
    fromDate
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
.cls-card {
  padding-top: 16px;
}
.cls-creator  {
  padding-left: 1rem;
  font-size: 12px;
  line-height: 36px;
  color: #999;
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
  margin: .5rem 1rem .5rem 0;
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
  font-size: 16px;
  padding: 5px 0;
  white-space: pre-wrap;
}
</style>