@extends('main')

@section('content')
<div id="app" class="container">
    <h1>Vondst # @{{ find.id }}</h1>
    <div class="row top-buffer">
        <div class="col-md-2">
            Titel
        </div>
        <div class="col-md-10">
            @{{ find.title }}
        </div>
        <div class="col-md-2">
            Category
        </div>
        <div class="col-md-10">
            @{{ find.category }}
        </div>
        <div class="col-md-2">
            Description
        </div>
        <div class="col-md-10">
            @{{ find.description }}
        </div>
        <div class="col-md-2">
            Dimension
        </div>
        <div class="col-md-10">
            @{{ find.dimension }}
        </div>
    </div>

    <div class="row top-buffer">
        <form id="feedbackform" @submit.prevent="handleForm">
            <div class="form-group row">
                <label for="feedback" class="col-sm-2 form-control-label">Feedback</label>
                <div class="col-sm-6">
                    <textarea v-model="feedback" type="text" class="form-control" id="feedback" rows=10></textarea>
                </div>
            </div>
            <div class="col-sm-2">
            </div>
            <div class="col-sm-6 checkbox">
                <label>
                    <input type="checkbox">Dit is een vondst onder embargo.
                </label>
            </div>
        </form>
    </div>
</div>

@stop

@section('script')
<script>
    new Vue({
        el: '#app',

        methods : {
            handleForm : function () {

            }
        },

        data : {
            find : {
                id : 395,
                title : "Gouden Romeinse munt",
                category : "munt",
                description : "Een gouden munt uit de vroege romeinse tijd.",
                dimension : "5x5 cm"
            },

            feedback : ""
        }
    });
</script>
@stop