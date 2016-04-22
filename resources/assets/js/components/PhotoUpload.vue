<template>
  <div class="photo-upload">
    <div class="photo-upload-cover">
      <i class="ui upload icon"></i> Klik hier om foto's toe te voegen of sleep foto's in dit veld
    </div>
    <input class="photo-upload-inp" type="file" accept="image/*" multiple @change="onFileChange">
    <div class="photo-upload-img" v-for="image in photograph">
      <img :src="image.identifier">
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
    position: absolute;
    z-index: 2;
    top:0;
    left:0;
    right:0;
    height: 60px;
    display: flex;
    align-items:center;
    justify-content: center;
    pointer-events: none;
    background: #fff;
    border-radius: 4px;
    border: 5px dotted #ccc;
    .dragging & {
      background: #FFD700;
      border-color: transparent;
    }
  }
  .photo-upload-inp {
    padding:10px;
    width:100%;
    height:60px;
    line-height:1em;
    text-align: center;
    outline: none;
  }
  .photo-upload-img {
    margin: .5rem 0;
    float: left;
    min-width: 100px;
    margin-right: 1rem;
    >img {
      display: block;
      height: 100px;
    }
  }
</style>
