@extends('main')

@section('content')
<div id="app" class="container">
    <h1>Mijn vondsten</h1>

    <div class="row find-row" v-for="find in finds">
        <div class="col-md-2">
            <a href="validate/15" class="thumbnail">
                <img src="{{ asset('assets/img/thumbnail_coin.jpg') }}">
            </a>
        </div>

        <div class="col-md-6">
            <p><b>Titel: </b>@{{ find.title }}</p>
        </div>
        <div class="col-md-6">
            <p><b>Categorie: </b>@{{ find.category }}</p>
        </div>
        <div class="col-md-6">
            <p><b>Beschrijving: </b>@{{ find.description }}</p>
        </div>

        <div class="col-md-6">
            <p><b>Status: </b>@{{ find.status }}</p>
        </div>
        <div class="col-md-6" v-if="find.status == 'In revisie'">
            <a href="myfinds/revision">Pas aan</a>
        </div>
    </div>
</div>
@stop

@section('script')
<script>
    new Vue({
        el: '#app',

        methods : {
            getDetail : function () {
                window.location.href = "validate/15";
            }
        },

        data : {
            viewRoles : [
            "Onderzoeker",
            "Vondstexpert"
            ],
            finds : [
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "In revisie"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Verwijderd door een admin"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Onder embargo geplaatst"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            },
            {
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                status : "Gepubliceerd"
            }
            ]
        }
    });
</script>
@stop