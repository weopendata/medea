@extends('main')

@section('content')
<div class="full" id="app">
    <div class="container">
        <div class="row text-center landing">
            <h2>Welkom op het MEDEA platform, een community platform dat experten, onderzoekers en detectoristen samen brengt.</h2>
        </div>
        <div class="row text-center">
            <a class="btn btn-default" data-toggle="modal" data-target="#registerModal">Registreer</a>
            <a class="btn btn-default" data-toggle="modal" data-target="#logInModal">Aanmelden</a>
        </div>
    </div>

    <div id="logInModal" class="modal fade" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Aanmelden</h4>
            </div>
            <div class="modal-body">
                <div class="form-group row">
                    <label for="email" class="col-sm-2 form-control-label">Email</label>
                    <div class="col-sm-6 col-sm-offset-1">
                        <input v-model="email" type="email" class="form-control" id="email" placeholder="">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="email" class="col-sm-2 form-control-label">Wachtwoord</label>
                    <div class="col-sm-6 col-sm-offset-1">
                        <input v-model="password" type="password" class="form-control" id="password" placeholder="">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">Aanmelden</button>
            </div>
        </div>

    </div>
    </div>

    <div id="registerModal" class="modal fase" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Registreer</h4>
                </div>
                <div class="modal-body">
                    <div id="form_div">
                        <form id="register_form" @submit.prevent="handleForm">
                            <div class="form-group row">
                                <label for="title" class="col-sm-2 form-control-label">Titel</label>
                                <div class="col-sm-6 col-sm-offset-1">
                                    <select v-model="title" id="title" name="title" class="form-control">
                                        <option selected></option>
                                        <option>De Heer</option>
                                        <option>Mevrouw</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="first_name" class="col-sm-2 form-control-label">Voornaam</label>
                                <div class="col-sm-6 col-sm-offset-1">
                                    <input v-model="first_name" type="text" class="form-control" id="first_name" placeholder="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="last_name" class="col-sm-2 form-control-label">Achternaam</label>
                                <div class="col-sm-6 col-sm-offset-1">
                                    <input v-model="last_name" type="text" class="form-control" id="last_name" placeholder="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="email" class="col-sm-2 form-control-label">Email</label>
                                <div class="col-sm-6 col-sm-offset-1">
                                    <input v-model="email" type="email" class="form-control" id="email" placeholder="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="role" class="col-sm-2 form-control-label">Rol</label>
                                <div class="col-sm-3 col-sm-offset-1">
                                    <select v-model="role" id="role" name="role" class="form-control">
                                        <option selected></option>
                                        <option>Detectorist</option>
                                        <option>Onderzoeker</option>
                                        <option>Vondstexpert</option>
                                        <option>Registrator</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="role" class="col-sm-2 form-control-label">Bio</label>
                                <div class="col-sm-6 col-sm-offset-1">
                                    <textarea id="description" name="description" class="form-control" rows="10">
                                    </textarea>
                                    <p class="help-block">@{{ description_text }}</p>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="privacy" class="col-sm-3 form-control-label">Privacy</label>
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
                                    <p class="help-block">Geef aan hoe uw naam en contactgegevens zichtbaar mogen zijn op gepubliceerde vondstfiches.</p>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-default">Registreer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- register modal -->

</div>
</div>
@stop

@section('script')
<script>
new Vue({
    el : '#app',

    data : {
        email : "",
        title : "",
        first_name : "",
        last_name : "",
        role : String,
        description_text : "Schrijf een korte bio en/of iets over je expertisedomein.",
        privacy : String
    },

    methods : {
        handleForm : function () {
            $('#registerModal').modal('toggle');
        }
    }
});
</script>
@stop