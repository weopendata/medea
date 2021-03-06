<template>
  <div class="card card-center cls-card">
    <div class="cls-type" v-if="cls.productionClassificationType">Classificatietype: <b>{{cls.productionClassificationType}}</b></div>
    <div class="card-textual">
      <p v-if="cls.productionClassificationValue || cls.productionClassificationCulturePeople || cls.productionClassificationRulerNation || cls.startDate || cls.endDate">
        <span class="cls-labeled" v-if="cls.productionClassificationValue">Type <b>{{cls.productionClassificationValue}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationCulturePeople">Periode <b>{{cls.productionClassificationCulturePeople}}</b></span>
        <span class="cls-labeled" v-if="cls.productionClassificationRulerNation">Heerser <b>{{cls.productionClassificationRulerNation}}</b></span>
        <span class="cls-labeled" v-if="cls.startDate||cls.endDate">Datering <b>{{ vC(cls.startDate) }} - {{ vC(cls.endDate, cls.startDate) }}</b></span>
      </p>
      <p class="cls-p" v-if="cls.productionClassificationDescription" v-text="cls.productionClassificationDescription"></p>
      <p v-if="singlePub">
        Bron:
        {{ citeClassificationPublication(singlePub) }}
        {{ (cls.productionClassificationSource && cls.productionClassificationSource[0] && cls.productionClassificationSource[0] != '__no_pages_specified__' ? ', spec. ' + cls.productionClassificationSource[0] + '.' : '') }}
        <a :href="singlePub.publicationContact" v-if="singlePub.publicationContact" v-text="singlePub.publicationContact"></a>
      </p>
      <p v-if="multiPub">
        Bronnen:
        <span v-for="(pub, index) in pubs" style="display:block">
          {{ citeClassificationPublication(pub) }}
          {{ publicationPages(index) }}
          <a :href="pub.publicationContact" v-if="pub.publicationContact" v-text="pub.publicationContact"></a>
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
      </span>
      <span class="cls-creator" v-if="creator||cls.created_at">
        Opgesteld
        <span v-if="creator">door {{creator}}</span>
        <time v-if="cls.created_at" :title="cls.created_at">op {{cls.created_at | fromDate}}</time>
        <time v-if="cls.updated_at!==cls.created_at" :title="cls.updated_at" style="margin-left:1em">update op {{cls.updated_at | fromDate}}</time>
      </span>
      <div style="float:right">
        <button class="btn" @click.prevent.stop="rm" v-if="removable"><i class="trash icon"></i></button>
      </div>
    </div>
  </div>
</template>

<script>
import { fromDate, urlify, fromPublication } from '../const.js'

import Publication from '../mixins/Publication.js'

export default {
  props: ['cls', 'obj'],
  mixins: [Publication],
  mounted () {
    this.user = window.medeaUser || {};
  },
  data () {
    return {
      user: {}
    }
  },
  computed: {
    removable () {
      return this.user.administrator || this.cls.addedByUser
    },
    creator () {
      return this.cls && this.cls.addedBy
    },
    multiPub () {
      return this.pubs && this.pubs.length > 1
    },
    singlePub () {
      return this.pubs && this.pubs.length === 1 && this.pubs[0]
    },
    pubs () {
      return (this.cls.publication || []).map(fromPublication)
    }
  },
  methods: {
    publicationPages (index) {
      return (this.cls.productionClassificationSource && this.cls.productionClassificationSource[index] && this.cls.productionClassificationSource[index] != '__no_pages_specified__' ? ", spec. " + this.cls.productionClassificationSource[index] + '.' : '')
    },
    citeClassificationPublication (pub) {
      return this.cite(pub)
    },
    vC (y1, y2) {
      if (y1 < 0 || y2 < 0) {
        return !y1 ? '?' : y1 < 0 ? -y1 + ' v.C.' : y1 + ' n.C.'
      }
      return y1 || '?'
    },
    agree () {
      if (this.cls.me) {
        this.cls[this.cls.me]--;
      }

      var agreedValue = this.cls.me === 'agree' ? null : 'agree';

      // $set
      this.cls.me =  agreedValue;

      if (this.cls.me) {
        this.cls[this.cls.me]++;
      }

      if (this.cls.me) {
        axios.post('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/agree');
      } else {
        axios.delete('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/agree');
      }
    },
    disagree () {
      if (this.cls.me) {
        this.cls[this.cls.me]--;
      }

      var disagreedValue = this.cls.me === 'disagree' ? null : 'disagree';

      // $set
      this.cls.me =  disagreedValue;

      if (this.cls.me) {
        this.cls[this.cls.me]++;
      }

      if (this.cls.me) {
        axios.post('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/disagree');
      } else {
        axios.delete('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1) + '/disagree');
      }
    },
    rm () {
      axios.delete('/objects/' + this.obj + '/classifications/' + (this.cls.identifier || -1))
        .then(response => {
          this.$emit('removed');
        })
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
.cls-type {
  padding-left: 1rem;
  font-size: 14px;
  line-height: 20px;
  color: #999;
  margin: .5rem 1rem .5rem 0;
  padding: 2px 2px 2px 1rem;
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
.select2-container {
  vertical-align: top!important;
}
.select2-container .select2-selection--single {
  height: 36px!important;
  border: 1px solid rgba(34, 36, 38, 0.15)!important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 34px!important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: 34px!important;
}
</style>
