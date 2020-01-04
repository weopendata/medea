<template>
  <div>
    <!-- the input field -->
    <input type="text"
    class="prompt"
    autocomplete="off"
    :placeholder="placeholder"
    :value="inputValue"
    @keydown.down="down"
    @keydown.up="up"
    @keydown.enter="hit"
    @keydown.esc="reset"
    @blur="update"
    @input="update"
    />

    <!-- the list of suggestions -->
    <ul v-show="hasItems" id="list" class="ui">
      <li class="item" v-for="(value, item) in items" :class="activeClass(value)" @mousedown="hit" @mousemove="setActive(value)">
        <span v-text="item"></span>
      </li>
    </ul>
  </div>
</template>

<script>
  import VueTypeahead from 'vue-typeahead'

  export default {
    mixins: [VueTypeahead],
    props: ["placeholder", "facet", "val"],
    data () {
      return {
      // The source url
      // (required)
      src: '/api/suggestions',

      // The data that would be sent by request
      // (optional)
      data: {
        facet: this.facet
      },

      // Limit the number of items which is shown at the list
      // (optional)
      limit: 5,

      // The minimum character length needed before triggering
      // (optional)
      minChars: 2,

      // Highlight the first item in the list
      // (optional)
      selectFirst: false,

      // Override the default value (`q`) of query parameter name
      // Use a falsy value for RESTful query
      // (optional)
      queryParamName: 'search'
    }
  },
  computed: {
   inputQuery () {
    if (this.val && this.facet == 'author') {
      var pieces = this.val.split('&')

      return pieces[pieces.length - 1].trim();
    }

    return this.val
  },
  inputValue () {
    return this.val;
  }
},
methods: {
  fetch () {
    if (!this.$http) {
      return util.warn('You need to provide a HTTP client', this)
    }

    if (!this.src) {
      return util.warn('You need to set the `src` property', this)
    }

    const src = this.queryParamName
    ? this.src
    : this.src + this.inputQuery

    const params = this.queryParamName
    ? Object.assign({ [this.queryParamName]: this.inputQuery }, this.data)
    : this.data

    let cancel = new Promise((resolve) => this.cancel = resolve)
    let request = this.$http.get(src, { params })

    return Promise.race([cancel, request])
  },
    // The callback function which is triggered when the user hits on an item
    // (required)
    onHit (item) {
      if (this.facet == 'author') {
        // Trim the query off the val
        var lastPost = this.val.lastIndexOf(this.inputQuery)
        this.val = this.val.substr(0, lastPost - 1)
        this.val += item.substr(this.inputQuery.length)
      } else {
        this.val = item
      }
    },

    // The callback function which is triggered when the response data are received
    // (optional)
    prepareResponseData (data) {
      return data
    },
    update (evt) {
      var value = evt.target.value;
      this.$emit('update', value);
    }
  }
}
</script>

<style>
#list ul {
  background-color: #fff;
  background: rgba(255,255,255,0);
  list-style: none;
  position: absolute;
  border: black;
  z-index: 999;
}
#list ul li {
  float: none;
  font-size: 18px;
  background-color: white;
  cursor: pointer;
  cursor: hand;
  border-bottom:1px solid #111312;
  z-index: 999;
}
</style>