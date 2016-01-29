@extends('main')

@section('content')
<div id="app" class="container">
    <div id="find-info" v-show="showDetails">
        <h1>Vondst # @{{ find.id }}</h1>

        <div class="row">
            <div class="clearfix">
                <a href="#" class="thumbnail">
                    <img src="{{ asset('assets/img/thumbnail_coin.jpg') }}">
                </a>
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">Titel</label>
            <div class="col-md-10">
                @{{ find.title }}
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">Category</label>
            <div class="col-md-10">
                @{{ find.category }}
            </div>
        </div>

        <div class="row top-buffer">
            <label class="col-sm-2 control-label">Description</label>
            <div class="col-sm-2">
                <label class="control-label">Afmeting</label>
            </div>

            <div class="col-sm-2">
                <label class="control-label">Hoeveelheid</label>
            </div>

            <div class="col-sm-2">
                <label class="control-label">Eenheid</label>
            </div>
        </div>

        <div v-for="dimension in find.dimension" class="row">
            <div class="col-md-2 col-md-offset-2">
                @{{ dimension.property }}
            </div>

            <div class="col-md-2">
                @{{ dimension.quantity }}
            </div>

            <div class="col-md-2">
                @{{ dimension.unit }}
            </div>
        </div>

        <div class="top-buffer">
            <form id="feedbackform" @submit.prevent="handleFeedback">
                <div class="form-group row">
                    <label for="feedback" class="col-sm-2 form-control-label">Feedback</label>
                    <div class="col-sm-6">
                        <textarea v-model="feedback" type="text" class="form-control" id="feedback" rows=10></textarea>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 form-control-label">Foto</label>
                    <div class="col-sm-6">
                        <select v-model="photoValidation" class="form-control">
                            <option>ok</option>
                            <option>onscherp</option>
                            <option>te kleine resolutie</option>
                            <option>onvoldoende ingezoomd</option>
                            <option>teveel ingezoomd</option>
                            <option>over/onderbelicht</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-8 col-sm-offset-2">
                        <label class="form-control-label">
                            Valideer deze vondstinformatie door de optie te kiezen die van toepassing is
                        </label>
                    </div>
                    <div class="col-sm-6 col-sm-offset-2">
                        <select v-model="validationOption" class="form-control">
                            <option value="ok">In orde: Informatie is volledig en correct, en mag gepubliceerd worden</option>
                            <option value="revision">Revisie nodig: Informatie is onvolledig of mogelijk niet correct, en moet herzien worden voor publicatie</option>
                            <option value="embargo">Embargo nodig: Informatie is gevoelig en mag voorlopig niet gepubliceerd worden</option>
                            <option value="frauduleus">Frauduleus: Informatie is mogelijk frauduleus en moet in dat geval verwijderd worden</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <button id="submit" type="submit" class="btn btn-success">@{{ feedback_button }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@stop

@section('script')
<script>
    new Vue({
        el: '#app',

        methods : {
            handleFeedback : function () {

                this.showDetails = false;

                div = '<div class="alert alert-success">';

                message = '';

                if (this.isRemoved) {
                    message = 'De vondst werd verwijderd en eventuele feedback werd teruggestuurd naar de vinder.';
                } else if (this.embargo && this.feedback.trim()) {
                    message = 'De feedback werd verstuurd naar de vinder, de bevoegde personen werden ook op de hoogte gebracht van de vondst onder embargo.';
                } else if (this.embargo) {
                    message = 'De vondst is onder embargo geplaatst en de bevoegde personen zijn op de hoogte gebracht. De vondst wordt niet publiek getoond maar is wel gevalideerd.';
                } else if (this.feedback.trim()) {
                    message = 'Er waren enkele fouten gevonden bij de input van de vinder, de feedback is verstuurd.';
                } else {
                    message = 'De vondst werd gevalideerd en werd publiek gemaakt.';
                }

                div += message + '</div>';

                $('#app').append(div);
            },

            evaluateFeedbackButton : function () {
                if (this.feedback.trim() || this.embargo || this.photoValidation != 'ok') {
                    this.feedback_button = "Stuur feedback";

                    $btn = $('#submit');

                    if (!$btn.hasClass('btn-warning')) {
                        $btn.removeClass('btn-success');
                        $btn.addClass('btn-warning');
                    }
                } else {
                    this.feedback_button = "Valideer";

                    if (!$btn.hasClass('btn-success')) {
                        $btn.removeClass('btn-warning');
                        $btn.addClass('btn-success');
                    }
                }
            }
        },

        data : {
            photoValidation : 'ok',
            viewRoles : [
            "Onderzoeker",
            "Vondstexpert"
            ],
            validationOption : '',
            find : {
                id : 15,
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : [
                    {
                        "unit" : "cm",
                        "quantity" : 5,
                        "property" : "diameter"
                    },
                    {
                        "unit" : "g",
                        "quantity" : 40,
                        "property" : "gewicht"
                    }
                ]
            },

            notifyAgency : false,
            feedback : "",
            feedback_button : "Valideer",
            embargo : false,
            isRemoved : false,
            showDetails : true
        },

        watch : {
            validationOption : function (val) {
                if (val == 'ok') {
                    this.feedback_button = 'Valideer';
                } else if (val == 'revision') {
                    this.feedback_button = 'Revisie nodig';
                } else if (val == 'frauduleus') {
                    this.feedback_button = 'Meld fraude';
                } else if (val == 'embargo') {
                    this.feedback_button = 'Plaats onder embargo';
                }
            }
        }
    });
</script>
@stop