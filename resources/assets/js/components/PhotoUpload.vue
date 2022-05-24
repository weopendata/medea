<template>
  <div class="photo-upload">
    <div class="photo-upload-img" v-for="(image, index) in photograph" @click="rm(index)" :class="{'photo-error':feedback[image.identifier]}" :title="'Foto '+(image.identifier||'nieuw')">
      <img :src="image.src">
      <ul v-if="feedback[image.identifier]">
        <li v-for="remark in imgRemarks[image.identifier]">{{ remark }}</li>
      </ul>
      <p v-if="feedback[image.identifier]&&!imgRemarks[image.identifier].length">Gelieve deze afbeelding te wijzigen.</p>
    </div>
    <div v-for="msg in warnings" class="ui warning message visible" v-text="msg"></div>
    <div class="photo-upload-cover">
      <div>
        <i class="ui upload big large icon"></i>
        <br>Klik hier om foto's toe te voegen of&nbsp;sleep&nbsp;foto's in dit veld
      </div>
      <input class="photo-upload-inp" type="file" accept="image/*" multiple @change="onFileChange">
    </div>
  </div>
</template>

<script>
import {MAX_FILE_SIZE, toBytes} from '../const.js'

export default {
  props: ['photograph'],
  data () {
    return {
      warnings: []
    }
  },
  computed: {
    feedback () {
      return this.$parent.$parent.f
    },
    imgRemarks () {
      return this.$parent.$parent.validation.imgRemarks
    }
  },
  mounted () {
    /*var container = $(this.$el)
    $('.photo-upload-inp', container).on('dragenter dragover', function () {
      container.addClass('dragging')
    })
    $('.photo-upload-inp', container).on('drop dragend dragleave', function () {
      container.removeClass('dragging')
    })*/
  },
  methods: {
    warn (...msg) {
      this.warnings.push(msg.join(' '))
    },
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files
      if (!files.length) {
        return this.warn('Kies een afbeelding.')
      }

      // Clear warnings
      this.warnings = []

      // Validate all selected files
      for (var i = 0; i < files.length; i++) {
        this.createImage(files[i])
      }
    },
    createImage(file) {
      var image = new Image
      var reader = new FileReader
      if (file.type.substr(0, 5) !== 'image') {
        return this.warn('Dit bestand wordt niet ondersteund:', file.type.substr(0, 5), '\nBestand:', file.name)
      }
      if (file.size > MAX_FILE_SIZE) {
        return this.warn('Dit bestand is te groot:', toBytes(file.size), '(max. ' + toBytes(MAX_FILE_SIZE) + ')', '\nBestand:', file.name)
      }

      // Read file and load it as an image
      reader.onload = (e) => {
        image.onload = () => {
          if (image.width < 400 || image.height < 400) {
            return this.warn('Resolutie is te klein:', image.width, 'x', image.height, '(min. 400 x 400)', '\nBestand:', file.name)
          }

          // It's valid
          this.photograph.push({
            name: file.name,
            size: file.size,
            width: image.width,
            height: image.height,
            src: reader.result
          })
        }
        image.src = reader.result;
      }
      reader.readAsDataURL(file)
    },
    rm: function (index) {
      if (confirm('Deze foto verwijderen?')) {
        this.photograph.splice(index, 1)
      }
    }
  }
}
</script>

<style lang="scss">
  .photo-upload {
    position:relative;
    &:after {
      content: "";
      display: table;
      clear: both;
    }
    .warning {
      clear: both;
      white-space: pre-wrap;
    }
  }
  .photo-upload-cover {
    position: relative;
    display: inline-flex;
    align-items:center;
    justify-content: center;
    float: left;
    margin: 0 1rem 1rem 0;
    border: 5px dotted #ccc;
    border-radius: 4px;
    padding: 0 1rem;
    height: 160px;
    max-width: 240px;
    text-align: center;
    pointer-events: none;
    background: #fff;
    .dragging & {
      background: #FFD700;
      border-color: transparent;
    }
  }
  .photo-upload-inp {
    position: absolute;
    z-index: 2;
    top:0;
    left:0;
    right:0;
    bottom: 0;
    opacity: 0;
    pointer-events: auto;
  }
  .photo-upload-img {
    position: relative;
    margin: 0 1rem 1rem 0;
    float: left;
    min-width: 10px;
    background: #fff;
    cursor: pointer;
    >img {
      display: block;
      max-height: 160px;
    }
    &::before {
      transition: opacity .3s;
      content: '';
      position:absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      opacity: 0;
      background: #f00;
      pointer-events: none;
    }
    &::after {
      transition: transform .3s;
      content: "\00d7";
      position:absolute;
      bottom: 1px;
      right: 1px;
      width: 1em;
      height: 1em;
      font-size: 16px;
      line-height: 1em;
      border-radius: .5em;
      text-align: center;
      font-weight: bold;
      color: white;
      background: red;
      pointer-events: none;
    }
    &:hover {
      &::before {
        opacity: .4;
      }
      &::after {
        transform: scale(2);
      }
    }
  }
  .photo-error {
    border: 2px solid red;
    padding: 1em;
  }
</style>
