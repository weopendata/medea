@extends('main')

@section('content')
<div id="app" class="container">
    <div id="find-info" v-show="showForm">
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
            <div class="col-sm-2">
                @{{ find.title }}
            </div>
        </div>

        <div class="row">
            <label class="col-sm-2 control-label">Beschrijving</label>
            <div class="col-sm-2">
                @{{ find.description }}
            </div>
        </div>

        <div class="row top-buffer">
            <label class="col-sm-2 control-label">Dimensie</label>
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

        <div class="row">
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
    </div>

    <div class="row top-buffer">
        <h2>Classificeer</h2>
        <form id="feedbackform" @submit.prevent="classify">
            <div class="form-group row">
                <label for="feedback" class="col-sm-2 form-control-label">Categorie</label>
                <div class="col-sm-6">
                    <select v-model="find.category" class="form-control" id="category">
                        <option selected></option>
                        <option>munt</option>
                        <option>mantelspeld</option>
                        <option>vingerhoed</option>
                        <option>muntgewicht</option>
                        <option>gesp</option>
                        <option>riemtong</option>
                        <option>bijl</option>
                        <option>pijlpunt</option>
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

            <div class="form-group row">
                <label class="col-sm-2" for="reference">Referenties</label>

                <div class="col-sm-2 col-md-offset-6">
                    <button @click.prevent="addReference" class="btn btn-center btn-success"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
                </div>
            </div>

            <div class="form-group row" v-for="reference in find.references" track-by="$index">
               <reference :index="$index" :reference.sync="reference"></reference>
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
                <button @click="disagree($index)" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span> @{{ classification.disagree }}</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <label class="control-label">Categorie</label>
            </div>
            <div class="col-md-4">
                @{{ classification.category }}
            </div>
        </div>

        <div class="row">
            <div class="col-md-2">
                <label class="control-label">Datering</label>
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
                <label class="control-label">Referenties</label>
            </div>
            <div v-for="reference in classification.references" track-by="$index">
                <div class="col-sm-6">
                    <a href="@{{ reference }}">@{{ reference }}</a>
                </div>
            </div>
        </div>

    </div>
</div>

<template id="reference-template">
    <div class="col-md-offset-2 col-md-6">
        <input v-model="reference" type="text" class="form-control" id="reference" placeholder="">
    </div>

    <div class="col-sm-2">
        <button @click.prevent="removeReference" class="btn btn-center btn-danger"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
    </div>
</template>
@stop

@section('script')
<script>
    Vue.component('reference', {
        template : '#reference-template',

        props : ['reference', 'index'],

        methods : {
            removeReference : function () {
                this.$parent.find.references.splice(this.index, 1);
            }
        }

    });

    new Vue({
        el: '#app',

        methods : {
            classify : function (e) {
                this.showForm = false;

                div = '<div class="alert alert-success">De classificatie is toegevoegd aan de vondst.</div>';

                $('#app').append(div);
            },

            addReference : function (e) {
                this.find.references.push("");
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
                ],
                year : "",
                references : [
                ""
                ]
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

                category : "munt",
                agree : 5,
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

                category : "mantelspeld",
                agree : 1,
                disagree: 14
            }
            ],

            showForm : true,
        }
    });

Vue.config.debug = true;
</script>
@stop