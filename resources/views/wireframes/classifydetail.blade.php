@extends('main')

@section('content')
<div id="app" class="container">
    <div id="find-info" v-show="showForm">
        <h1>Vondst # @{{ find.id }}</h1>
        <div class="row top-buffer">
            <div class="col-md-2">
                <a href="#" class="thumbnail">
                    <img src="{{ asset('assets/img/thumbnail_coin.jpg') }}">
                </a>
            </div>
            <div class="col-md-10">
                Titel: @{{ find.title }}
            </div>
            <div class="col-md-10">
                Beschrijving: @{{ find.description }}
            </div>
            <div class="col-md-10">
                Dimensie: @{{ find.dimension }}
            </div>
        </div>

        <div class="row top-buffer">
            <h2>Classificeer</h2>
            <form id="feedbackform" @submit.prevent="classify">
                <div class="form-group row">
                    <label for="feedback" class="col-sm-2 form-control-label">Categorie</label>
                    <div class="col-sm-6">
                        <select v-model="find.category" class="form-control" id="category">
                            <option selected></option>
                            <option>Categorie 1</option>
                            <option>Categorie 2</option>
                            <option>Categorie 3</option>
                            <option>Categorie 4</option>
                            <option>Categorie 5</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="date" class="col-sm-2 form-control-label">Datering</label>
                    <div class="col-sm-2">
                        <input v-model="find.start_year" class="form-control" id="category" type="text">
                    </div>

                    <p class="text-center col-md-1">-</p>

                    <div class="col-sm-2">
                        <input v-model="find.end_year" class="form-control" id="category" type="text">
                    </div>

                    <div class="col-sm-1">
                        <select v-model="find.era" class="form-control">
                            <option selected>v. Chr.</option>
                            <option>n. Chr.</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row" id="references">
                    <label class="col-sm-2" for="reference">Referenties</label>
                    <div class="col-sm-6">
                        <input v-model="find.references" class="form-control" type="text">
                    </div>
                    <div class="col-sm-2">
                        <button @click="addReference" class="btn btn-center btn-success"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-2">
                        <button id="submit" type="submit" class="btn btn-success">Classificeer</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row top-buffer">
            <h2>Classificaties</h2>
            <div class="classification" v-for="classification in classifications" track-by="$index">
                <div class="row">
                    <div class="col-sm-2">
                        <h3>Classificatie #@{{ $index +1 }}</h3>
                    </div>
                </div>

                <div class="row btn-vote">
                    <div class="col-sm-2">
                        <button @click="agree($index)" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> @{{ classification.agree }}</button>
                        <button @click="disagree($index)" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> @{{ classification.disagree }}</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        Categorie
                    </div>
                    <div class="col-md-4">
                        @{{ classification.category }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        Datering
                    </div>
                    <div class="col-md-2">
                        <span>@{{ classification.date.start_year }} - @{{ classification.date.end_year }}</span>
                         <span class="text-center" v-if="classification.date.era == 1">
                            n. Chr.
                        </span>
                        <span class="text-center" v-if="classification.date.era == 0">
                            v. Chr.
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-2">
                        Referenties
                    </div>
                    <div v-for="reference in classification.references" track-by="$index">
                        <div class="col-sm-6" v-if="$index == 0">
                            @{{ reference }}
                        </div>

                        <div class="col-sm-6 col-md-offset-2" v-if="$index == 1">
                            @{{ reference }}
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@stop

@section('script')
<script>
    new Vue({
        el: '#app',

        methods : {
            classify : function (e) {
                this.showForm = false;

                div = '<div class="alert alert-success">De classificatie is toegevoegd aan de vondst.</div>';

                $('#app').append(div);
            },

            addReference : function (e) {
                e.preventDefault();

                div = '<div class="col-sm-6 col-md-offset-2 reference-row"><input v-model="find.references" class="form-control" type="text"></div>';

                $('#references').append(div);
            },

            agree : function (index) {
                this.classifications[index].agree += 1;
            },

            disagree : function (index) {
                this.classifications[index].disagree += 1;
            }
        },

        data : {
            find : {
                id : 15,
                title : "Gouden Romeinse munt",
                category: "",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm",
                year : "",
                references : []
            },

            classifications : [
                {
                    date : {
                        start_year : 5,
                        end_year : 50,
                        era : 1
                    },
                    references : [
                        "http://onlinelibrary.wiley.com/doi/10.1111/apaa.12000/full",
                        "http://onlinelibrary.wiley.com/doi/10.1111/apaa.12000/full"
                    ],

                    category : "categorie 1",
                    agree : 1,
                    disagree: 0
                },
                {
                    date : {
                        start_year : 5,
                        end_year : 50,
                        era : 1
                    },
                    references : [
                        "http://onlinelibrary.wiley.com/doi/10.1111/apaa.12000/full"
                    ],

                    category : "categorie2",
                    agree : 5,
                    disagree: 0
                }
            ],

            showForm : true,
        },

        watch : {
            'find.category' : function (val, oldVal) {
                console.log(val);
            }
        }
    });
</script>
@stop