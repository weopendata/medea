<template>
  <div class="photo-upload">
    <div class="photo-upload-img" v-for="image in photograph">
      <img :src="image.resized || image.identifier">
    </div>
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
export default {
  props: ['photograph'],
  attached () {
    var container = $(this.$el)
    $('.photo-upload-inp', container).on('dragenter dragover', function () {
      container.addClass('dragging');
    })
    $('.photo-upload-inp', container).on('drop dragend dragleave', function () {
      console.log('dra')
      container.removeClass('dragging');
    })

  },
  methods: {
    onFileChange(e) {
      var files = e.target.files || e.dataTransfer.files;
      if (!files.length)
        return;
      for (var i = 0; i < files.length; i++) {
        this.createImage(files[i]);
      }
    },
    createImage(file) {
      var image = new Image();
      var reader = new FileReader();
      var vm = this;
      if (file.type.substr(0, 5) !== 'image') {
        return console.warn(file.name, 'has unsupported type:', file.type.substr(0, 5));
      }
      if (file.size > 1024*1024*4) {
        return console.warn(file.name, 'is too large:', file.size);
      }

      reader.onload = (e) => {
        vm.photograph.push({
          name: file.name,
          size: file.size,
          identifier: e.target.result
        });
      };
      reader.readAsDataURL(file);
    },
    removeImage: function (e) {
      this.photograph = [];
    }
  }
}
</script>

<style lang="sass">
  .photo-upload {
    position:relative;
    overflow: auto;
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
    margin: 0 1rem 1rem 0;
    float: left;
    min-width: 10px;
    >img {
      display: block;
      max-height: 160px;
    }
  }
</style>
