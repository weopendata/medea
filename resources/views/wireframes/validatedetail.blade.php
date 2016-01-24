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
                    <div class="col-sm-2">
                    </div>
                    <div class="col-sm-6 checkbox">
                        <label>
                            <input v-model="embargo" type="checkbox">Dit is een vondst onder embargo.
                        </label>
                    </div>
                </div>

                <div class="form-group row">
                    <button id="submit" type="submit" class="btn btn-success">@{{ feedback_button }}</button>
                    <button id="remove" type="submit" @click.prevent="remove" class="btn btn-danger">Verwijder</button>
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
                if (this.feedback.trim() || this.embargo) {
                    this.feedback_button = "Stuur feedback";
                    console.log("fb!");
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
            },

            remove : function (e) {
                remove = window.confirm("Ben je zeker dat je deze vondst wil verwijderen?");

                if (remove == true) {
                    this.isRemoved = true;
                    this.handleFeedback();
                }
            }
        },

        data : {
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
            feedback : function (val) {
                this.evaluateFeedbackButton();
            },
            embargo : function () {
                this.evaluateFeedbackButton();
            }
        }
    });
</script>
@stop