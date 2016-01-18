@extends('main')

@section('content')
<div id="app">
    <div class="container" v-show="showForm == true">
        <h2>Nieuwe vondst</h2>
        <form id="new_find" @submit.prevent="handleForm">
            <div class="form-group row">
                <label for="title" class="col-sm-2 control-label">Titel</label>
                <div class="col-sm-6">
                    <input v-model="title" type="title" class="form-control" id="title" placeholder="">
                </div>
            </div>

            <div class="form-group row">
                <label for="description" class="col-sm-2 control-label">Beschrijving</label>
                <div class="col-sm-6">
                    <textarea v-model="description" type="text" class="form-control" id="description" rows="10">
                    </textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="dimension" class="col-sm-2 control-label">Dimensie</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <input v-model="dimension" type="text" class="form-control" id="dimension" placeholder=""><span class="input-group-addon">cm</span>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-md-2">
                    <button id="submit" type="submit" class="btn btn-success">Voeg toe</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('script')
<script>
    new Vue({
      el: '#app',

      data: {
        showForm : true
      },

      methods : {
        handleForm : function () {
            this.showForm = false;

            div = '<div class="alert alert-success">Uw vondst werd geregistreerd. Experten en andere leden van het platform zullen de vondst classificeren en valideren.</div>';

            $('#app').append(div);
        }
    }
});
</script>
@stop