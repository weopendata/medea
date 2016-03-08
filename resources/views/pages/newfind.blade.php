@extends('main')

@section('content')
<div class="ui two column centered grid">
    <div class="column">
        {!! Form::open(array('url' => 'finds', 'files' => true)) !!}
        {!! Form::token() !!}

        <div class="ui form segment">
            <div class="inline fields">
                <div class="three wide field">
                    <label>Beschrijving</label>
                </div>
                <div class="eight wide field">
                    <textarea></textarea>
                </div>
            </div>

            <div class="inline fields">
                <div class="three wide field">
                    <label>Beschrijving</label>
                </div>
                <div class="eight wide field">
                    <textarea></textarea>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
</div>
@endsection