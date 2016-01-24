var Vue = require('vue');

/*
var vm = new Vue({
  el: "#app",
  components: {
    "vue-datetime-picker": require("vue-datetime-picker")
  },
  data: {
    result1: null,
    result2: null,
    result3: null,
    startDatetime: moment(),
    endDatetime: null
  },
  methods: {
    formatDatetime: function(datetime) {
      if (datetime === null) {
        return "[null]";
      } else {
        return datetime.format("YYYY-MM-DD HH:mm:ss");
      }
    },
    formatDate: function(date) {
      if (date === null) {
        return "[null]";
      } else {
        return date.format("YYYY-MM-DD");
      }
    },
    formatTime: function(time) {
      if (time === null) {
        return "[null]";
      } else {
        return time.format("HH:mm:ss");
      }
    },
    onStartDatetimeChanged: function(newStart) {
      var endPicker = this.$.endPicker.control;
      endPicker.minDate(newStart);
    },
    onEndDatetimeChanged: function(newEnd) {
      var startPicker = this.$.startPicker.control;
      startPicker.maxDate(newEnd);
    }
  }
});*/