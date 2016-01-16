@extends('main')

@section('content')
<div id="app">
    <div id="form_div" class="container">
        <h2>Registreer</h2>
        <form id="register_form" @submit.prevent="handleForm">
            <div class="form-group row">
                <label for="email" class="col-sm-2 form-control-label">Email</label>
                <div class="col-sm-6">
                    <input v-model="email" type="email" class="form-control" id="email" placeholder="">
                </div>
            </div>

            <div class="form-group row">
                <label for="first_name" class="col-sm-2 form-control-label">Voornaam</label>
                <div class="col-sm-6">
                    <input v-model="first_name" type="text" class="form-control" id="first_name" placeholder="">
                </div>
            </div>

            <div class="form-group row">
                <label for="last_name" class="col-sm-2 form-control-label">Achternaam</label>
                <div class="col-sm-6">
                    <input v-model="last_name" type="text" class="form-control" id="last_name" placeholder="">
                </div>
            </div>

            <div class="form-group row">
                <label for="role" class="col-sm-2 form-control-label">Rol</label>
                <div class="col-sm-6">
                    <select id="role" name="role" class="form-control">
                        <option selected></option>
                        <option>Detectorist</option>
                        <option>Agentschap</option>
                        <option>Vondstexpert</option>
                        <option>Registrator</option>
                    </select>
                </div>
            </div>

            <div class="col-sm-2">
            </div>
            <div class="checkbox row">
                <label for="privacy" class="col-sm-6 form-control-label">
                    <input type="checkbox" id="checkbox">Mijn naam mag gedeeld worden op mijn vondsten.
                </label>
            </div>

            <button type="submit" class="btn btn-default">Registreer</button>
  </form>
</div>
</div>
@stop


@section('script')
<script>
new Vue({
    el: '#app',

    data : {
        email : "",
        first_name : "",
        last_name : "",
        role : ""
    },

    methods : {
        handleForm : function (e) {
            $('#form_div').hide();

            div = '<div class="alert alert-success">Beste ' + this.first_name  + ' ' + this.last_name + ', bedankt om je te registreren. Een medewerker kijkt na of alles in orde is en stuurt je dan een bevestigingsmail terug via ' + this.email + '.</div>';

            $('#app').append(div);
        }
    }
});
</script>
@stop