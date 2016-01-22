@extends('main')

@section('content')
<div id="app">
    <div id="form_div" class="container">
        <h2>Registreer</h2>
        <form id="register_form" @submit.prevent="handleForm">
            <div class="form-group row">
                <label for="title" class="col-sm-2 form-control-label">Titel</label>
                <div class="col-sm-6">
                    <input v-model="title" type="text" class="form-control" id="title" placeholder="">
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
                <label for="email" class="col-sm-2 form-control-label">Email</label>
                <div class="col-sm-6">
                    <input v-model="email" type="email" class="form-control" id="email" placeholder="">
                </div>
            </div>

            <div class="form-group row">
                <label for="role" class="col-sm-2 form-control-label">Rol</label>
                <div class="col-sm-3">
                    <select v-model="role" id="role" name="role" class="form-control">
                        <option selected></option>
                        <option>Detectorist</option>
                        <option>Onderzoeker</option>
                        <option>Expert</option>
                        <option>Registrator</option>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="role" class="col-sm-2 form-control-label">Beschrijving</label>
                <div class="col-sm-6">
                    <textarea id="description" name="description" class="form-control" rows="10">
                    </textarea>
                    <p class="help-block">@{{ description_text }}</p>
                </div>
            </div>

            <div class="form-group row">
                <label for="privacy" class="col-sm-2 form-control-label">Privacy</label>
                <div class="col-sm-6">
                    <label for="privacy" class="form-control-label">
                        <select v-model="privacy" id="role" name="role" class="form-control">
                            <option selected></option>
                            <option id="">delen met iedereen</option>
                            <option id="">alleen delen met onderzoekers en de overheid</option>
                            <option id="">alleen delen met onderzoekers</option>
                            <option id="">alleen delen met onderzoekers na verzoek</option>
                            <option id="">alleen delen met alle geregistreerde gebruikers</option>
                        </select>
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-success btn-default">Registreer</button>
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
        title : "",
        first_name : "",
        last_name : "",
        role : String,
        description_text : "Schrijf een korte biografie.",
        privacy : String
    },

    methods : {
        handleForm : function (e) {
            $('#form_div').hide();

            div = '<div class="alert alert-success">Beste ' + this.first_name  + ' ' + this.last_name + ', bedankt om je te registreren. Een medewerker kijkt na of alles in orde is en stuurt je dan een bevestigingsmail terug via ' + this.email + '.</div>';

            $('#app').append(div);
        }
    },
    watch : {
        role : function (val, oldVal) {
            if (val == 'Detectorist') {
                this.description_text = 'Schrijf een korte biografie.';
            } else if (val == 'Onderzoeker') {
                this.description_text = 'Schrijf iets kort over je onderzoeksproject.';
            } else if (val == 'Expert') {
                this.description_text = 'Schrijf iets kort over jouw expertisedomein.';
            } else if (val == 'Registrator') {
                this.description_text = 'Schrijf iets kort over jouw expertisedomein.';
            } else {
                this.description_text = 'Schrijf een korte biografie.';
            }
        }
    }
});
</script>
@stop