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
                <label for="dimensions" class="col-sm-2 control-label">Dimensies</label>
                <div class="col-md-6">
                    <div class="col-sm-4">
                            <label control-label>bla</label>
                    </div>

                    <div class="col-sm-2">
                    <label control-label>sfdqs</label>
                    </div>

                    <div class="col-sm-3">
                        <label control-label>blqsfda</label>
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
        </div><!-- dimension unit symbols-->

        <div class="col-sm-2">
            <button @click.prevent="removeDimension" class="btn btn-center btn-danger"><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
        </div>
    </div>
</div>
</template>

@stop

@section('script')
<script>
    Vue.component('dimension', {
        template : '#dimension-template',

        props: ['dimension', 'units', 'symbols', 'index'],

        methods : {
            removeDimension : function () {
                this.$parent.dimensions.splice(this.index, 1);
            }
        }
    });

    new Vue({
      el: '#app',

      data: {
        showForm : true,
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
        ]
    },

    methods : {
        handleForm : function () {
            this.showForm = false;

            div = '<div class="alert alert-success">Uw vondst werd geregistreerd. Experten en andere leden van het platform zullen de vondst classificeren en valideren.</div>';

            $('#app').append(div);
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