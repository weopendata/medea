<template>
  <div class="ui action input">
    <input type="text" v-model="model.publicationTitle" placeholder="bibliografische referentie, URL, DOI, ...">
    <button type="button" class="ui button" @click="$parent.editPublication(model, index)">Bewerken</button>
  </div>
</template>

<script>
  // <div class="ui search fluid selection dropdown">
  //   <input type="hidden" name="user">
  //   <i class="dropdown icon"></i>
  //   <div class="default text">{{ model.identifier ? title : 'bibliografische referentie, URL, DOI, ...' }}</div>
  //   <div class="menu">
  //     <div class="item" data-value="jenny">Jenny Hess</div>
  //   </div>
  // </div>
// import 'semantic-ui-css/components/api.min.js'
// import 'semantic-ui-css/components/search.min.js'

//     <div class="entity-rm" v-if="model.identifier" @click.prevent="$emit('rm')"><i class="grey close icon" @click="start=''"></i></div>

export default {
  props: ['model', 'index'],
  computed: {
    title () {
      return this.model.publicationTitle
    }
  },
  methods: {
    input () {
    }
  },
  attached () {
    var self = this
    // var $elem = this.elem = $(this.$el).dropdown({
    //   // maxResults: 5,
    //   // searchDelay: 200,
    //   // onSelect (item) {
    //   //   self.model.to_name = item.title
    //   //   self.model.to = item.id
    //   //   self.$nextTick(function() {
    //   //     $elem.blur()
    //   //   })
    //   // },
    //   apiSettings: {
    //     url: '/apipublication.json?sd',
    //     // url: '/api/publications?q={query}',
    //     onComplete: function (data) {console.log(data)},
    //     onFailure: function (data) {console.log(data)},
    //     onError: function (data) {console.log(data)},
    //     onAbort: function (data) {console.log(data)},
    //     onResponse: function (data) {
    //       console.log('hey yo', data)
    //       return {
    //         results: []
    //       }
    //     }
    //   }
    // })
  }
}
</script>

<style>
.ui.form .entity {
  position: relative;
}
.ui.form .entity input {
}
.ui.form .entity-invalid input {
  border-color: red;
}
.ui.form .entity-valid input {
  border-color: #449d1f;
  color: #449d1f;
}
.entity-rm {
  position: absolute;
  z-index: 1;
  top: .8em;
  right: .5em;
}
.entity .edit-since {
  position: relative;
  z-index: 1;
  padding-left: 1em;
  padding-right: 1em;
}
.ui.form .entity input:focus::-webkit-input-placeholder {
  opacity: 1;
  color: #999;
}
.ui.form .entity input:focus::-moz-placeholder {
  opacity: 1;
  color: #999;
}
.ui.form .entity input:focus:-ms-input-placeholder {
  opacity: 1;
  color: #999;
}
.ui.form .entity input:focus:-moz-placeholder {
  opacity: 1;
  color: #999;
}
</style>