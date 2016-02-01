@extends('main')

@section('content')
<div id="app">
    <div class="container" v-show="showForm == true">
        <h2>Pas uw vondst aan</h2>
        <span class="help-block">Dit is een generieke vondst die je kan aanpassen en is niet consistent met andere data.</span>
        <form id="new_find">
            <div class="form-group row has-error">
                <label class="col-sm-2 control-label">Fotomateriaal*</label>
                <div class="col-sm-6">
                   <button class="btn btn-default" @click.prevent="showUpload">Upload foto's</button>
                </div>
            </div>

            <div class="form-group row">
                <label for="description" class="col-sm-2 control-label">Beschrijving</label>
                <div class="col-sm-6">
                    <textarea v-model="description" type="text" class="form-control" id="description" rows="10">"Een gouden munt uit de vroege romeinse tijd.</textarea>
                </div>
            </div>

            <div class="form-group row">
                <label for="datefind" class="col-sm-2 control-label">Tijdstip van de vondst</label>
                <div class="col-sm-2">
                    <input type="date" value="01/02/2016" v-model="findDate" class="form-control"/>
                </div>
            </div>

             <div class="form-group row">
                <label for="location" class="col-sm-2 control-label">Locatie</label>
                <div class="col-sm-2">
                    <label>breedtegraad</label>
                </div>
                <div class="col-sm-2">
                    <label>lengtegraad</label>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-offset-2 col-sm-2">
                    <input value="50.806905897875" v-model="location.lat" type="text" class="form-control">
                </div>
                <div class="col-sm-2">
                    <input value="3.3014484298127" v-model="location.lon" type="text" class="form-control">
                </div>
            </div>

            <div class="form-group row has-error">
            <label for="material" class="col-sm-2 control-label">Materiaal*</label>
                <div class="col-sm-6">
                    <div class="input-group error">
                        <select v-model="material" class="form-control">
                            <option v-for="material in materials" selected>@{{ material }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
            <label for="productionTechnique" class="col-sm-2 control-label">Techniek</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <select v-model="productionTechnique" class="form-control">
                            <option v-for="productionTechnique in productionTechniques" selected>@{{ productionTechnique }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
            <label class="col-sm-2 control-label">Oppervlaktebehandeling</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <select v-model="surfaceTreatment" class="form-control">
                            <option v-for="surfaceTreatment in surfaceTreatments" selected="">@{{ surfaceTreatment }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row has-error">
                <label for="dimensions" class="col-sm-2 control-label">Dimensies*</label>
                <div class="col-md-6">
                    <div class="col-sm-4">
                        <label control-label>Afmeting</label>
                    </div>

                    <div class="col-sm-2">
                        <label control-label>Hoeveelheid</label>
                    </div>

                    <div class="col-sm-3">
                        <label control-label>Eenheid</label>
                    </div>

                    <div class="col-sm-2">
                        <button @click.prevent="addDimension" class="btn btn-center btn-success"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
                    </div>
                </div>

                <div v-for="dimension in dimensions" track-by="$index">
                    <div class="col-md-offset-2 col-md-6 dimension-col">
                        <dimension :index="$index" :dimension.sync="dimension" :symbols="dimensionUnitSymbols" :units="dimensionUnits"></dimension>
                    </div>
                </div>
            </div><!-- dimensies -->

            <div class="row top-buffer">
                <h2>Classificeer</h2>
                <div class="form-group row">
                    <label for="feedback" class="col-sm-2 form-control-label">Categorie</label>
                    <div class="col-sm-6">
                        <select v-model="category" class="form-control" id="category">
                            <option></option>
                            <option selected>munt</option>
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
                        <input value="50" v-model="start_year" class="form-control" id="category" type="text">
                    </div>

                    <p class="text-center col-md-1">-</p>

                    <div class="col-sm-2">
                        <input value="150" v-model="end_year" class="form-control" id="category" type="text">
                    </div>

                    <div class="col-sm-1">
                        <select v-model="era" class="form-control">
                            <option>v. Chr.</option>
                            <option selected="">n. Chr.</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2" for="reference">Referenties</label>

                    <div class="col-sm-2 col-md-offset-6">
                        <button @click.prevent="addReference" class="btn btn-center btn-success"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
                    </div>
                </div>

                <div class="form-group row" v-for="reference in references" track-by="$index">
                 <reference :index="$index" :reference.sync="reference"></reference>
             </div>
         </form>

            <div class="form-group row">
                <button id="submit" type="submit" @click.prevent="validateFind" class="btn btn-success">Laten valideren</button>
                <button id="submit" type="submit" @click.prevent="saveFind" class="btn btn-warning">Bewaren</button>
            </div>
        </form>
    </div>
</div>

<template id="dimension-template">
    <div>
        <div class="col-sm-4">
            <div class="input-group">
                <select v-model="dimension.unit" class="form-control">
                    <option v-for="dimensionUnit in units">@{{ dimensionUnit }}</option>
                </select>
            </div><!-- dimension units -->

        </div>

        <div class="col-sm-2">
            <div class="input-group">
                <input v-model="dimension.quantity" type="text" class="form-control" id="dimensions" placeholder="">
            </div>
        </div>

        <div class="col-sm-3">
            <div class="input-group">
                <select v-model="dimension.symbol" class="form-control">
                    <option v-for="dimensionUnitSymbol in symbols">@{{ dimensionUnitSymbol }}</option>
                </select>
            </div>
        </div>

        <div class="col-sm-2">
            <button @click.prevent="removeDimension" class="btn btn-center btn-danger"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
        </div>
    </div>
</div>
</template>

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
    // Dimension component
    Vue.component('reference', {
        template : '#reference-template',

        props : ['reference', 'index'],

        methods : {
            removeReference : function () {
                this.$parent.references.splice(this.index, 1);
            }
        }

    });

    Vue.component('dimension', {
        template : '#dimension-template',

        props: ['dimension', 'units', 'symbols', 'index'],

        methods : {
            removeDimension : function () {
                this.$parent.dimensions.splice(this.index, 1);
            }
        }
    });

    // Datepicker directive
    Vue.directive('datepicker', {
          bind: function () {
            var vm = this.vm;
            var key = this.expression;
            $(this.el).datepicker({
              dateFormat: 'dd.mm.yy',
              onClose: function (date) {
                if (date.match(/^(0?[1-9]|[12][0-9]|3[01])[\/\-\.](0?[1-9]|1[012])[\/\-\.]\d{4}$/))
                  vm.$set(key, date);
              else {
                  vm.$set(key, "");
                  console.log('invalid date');
              }
          }
      });
        },
        update: function (val) {
            $(this.el).datepicker('setDate', val);
        }
    });

    new Vue({
      el: '#app',

      data: {
        category : '',
        findDate: '',
        showForm : true,
        category : String,
        material : String,
        viewRole : '',
        references : [
            ""
        ],

        era : '',

        productionTechnique : String,
        location : {
            "lat" : "",
            "lon" : ""
        },
        description : "",
        dimensionUnitSymbols : [
        "mm",
        "cm",
        "m",
        "g"
        ],
        dimensionUnits : [
        "lengte",
        "breedte",
        "diepte",
        "omtrek",
        "diameter",
        "gewicht"
        ],
        dimensions : [
        {
            'unit' : "",
            'quantity' : "",
            'symbol' : ""
        }
        ],
        categories : [
        "munt",
        "mantelspeld",
        "vingerhoed",
        "muntgewicht",
        "gesp",
        "riemtong",
        "bijl",
        "pijlpunt"
        ],
        materials : [
        "brons",
        "ijzer",
        "lood",
        "keramiek",
        "meerdere"
        ],
        productionTechniques : [
        "boetseren",
        "smeden",
        "gieten",
        "weven",
        "vlechten"
        ],
        surfaceTreatments : [
            'optie 1',
            'optie 2',
            'meerdere'
        ]
    },

    methods : {
        addReference : function (e) {
                this.references.push("");
        },

        validateFind : function () {
            this.showForm = false;

            div = '<div class="container"><div class="row text-center"><div class="alert alert-success col-md-12">Uw vondstfiche zal gevalideerd worden, waarna andere bezoekers van het platform toegang krijgen tot de beschrijving ervan en vondstexperten informatie kunnen toevoegen. Uw identiteitsgegevens en de precieze vondstlocatie worden afgeschermd voor niet-geautoriseerde gebruikers. U wordt op de hoogte gehouden van wijzigingen aan deze vondstfiche.</div></div></div>';

            $('#app').append(div);
        },

        saveFind : function () {
            this.showForm = false;

            div = '<div class="container"><div class="row text-center"><div class="alert alert-success col-md-12">Uw vondst werd bewaard en kan later nog aangepast worden. Eens u klaar bent met de fiche kan u deze laten valideren.</div></div></div>';

            $('#app').append(div);
        },

        addDimension : function () {
            this.dimensions.push({
                'unit' : "",
                'quantity' : "",
                'symbol' : ""
            });
        },

        showUpload : function () {
            window.alert("Op deze manier zal u foto's kunnen uploaden.");
        }
    },

    watch : {
        findDate : function (val) {
            console.log(val);
        }
    }
});
</script>
@stop