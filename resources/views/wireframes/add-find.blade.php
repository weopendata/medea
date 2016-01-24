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
                <label for="datefind" class="col-sm-2 control-label">Tijdstip van de vondst</label>
                <div class="col-sm-2">
                    <input type="text" class="form-control" v-datepicker="findDate"/>
                </div>
            </div>

             <div class="form-group row">
                <label for="location" class="col-sm-2 control-label">Locatie</label>
                <div class="col-sm-2">
                    <label>latitude</label>
                </div>
                <div class="col-sm-2">
                    <label>longitude</label>
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-offset-2 col-sm-2">
                    <input v-model="location.lat" type="text" class="form-control">
                </div>
                <div class="col-sm-2">
                    <input v-model="location.lon" type="text" class="form-control">
                </div>
            </div>


            <div class="form-group row">
                <label for="category" class="col-sm-2 control-label">Categorie</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <select v-model="category" class="form-control">
                            <option v-for="category in categories">@{{ category }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
            <label for="material" class="col-sm-2 control-label">Materiaal</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <select v-model="material" class="form-control">
                            <option v-for="material in materials">@{{ material }}</option>
                        </select>
                    </div>
                </div>
            </div>

             <div class="form-group row">
            <label for="productionTechnique" class="col-sm-2 control-label">Techniek</label>
                <div class="col-sm-6">
                    <div class="input-group">
                        <select v-model="productionTechnique" class="form-control">
                            <option v-for="productionTechnique in productionTechniques">@{{ productionTechnique }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label for="dimensions" class="col-sm-2 control-label">Dimensies</label>
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

            <div class="form-group row">
                <div class="col-md-2">
                    <button id="submit" type="submit" class="btn btn-success">Voeg toe</button>
                </div>
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

@stop

@section('script')
<script>
    // Dimension component
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
        findDate: '',
        showForm : true,
        category : String,
        material : String,
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
        "keramiek"
        ],
        productionTechniques : [
        "boetseren",
        "smeden",
        "gieten",
        "weven",
        "vlechten"
        ]
    },

    methods : {
        handleForm : function () {
            this.showForm = false;

            div = '<div class="alert alert-success">Uw vondst werd geregistreerd. Experten en andere leden van het platform zullen de vondst classificeren en valideren.</div>';

            $('#app').append(div);
        },

        checkDate : function () {
            console.log(this.findDate);
        },

        addDimension : function () {
            this.dimensions.push({
                'unit' : "",
                'quantity' : "",
                'symbol' : ""
            });
        }
    }
});
</script>
@stop