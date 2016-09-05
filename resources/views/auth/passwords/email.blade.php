@extends('main')

<!-- Main Content -->
@section('content')
<div style="max-width:25em;margin:0 auto;">
    <form class="card ui form" role="form" method="POST" action="{{ url('/password/email') }}">

        {!! csrf_field() !!}

        <div class="card-textual" style="padding-top:8px;">
            <div class="card-title">Reset wachtwoord</div>

            @if (session('message'))
            <div class="ui success message visible">
                <p>
                    {{ session('message') }}
                </p>
            </div>
            @endif

            @if ($errors->any())
            <div class="ui error message">
                <p>
                    {{ $errors->first() }}
                </p>
            </div>
            @endif

            <div class="field{{ $errors->has('email') ? ' error' : '' }}">
                <label>Emailadres</label>
                <input type="email" name="email" value="{{ old('email') }}">

                @if ($errors->has('email'))
                    <div class="ui negative message">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <p>
                We zullen je een email sturen met daarin instructies om je wachtwoord te wijzigen.
            </p>
        </div>
        <div class="card-bar">
            <button type="submit" class="btn btn-primary">
                <i class="envelope icon"></i> Wachtwoord-reset verzenden
            </button>
        </div>
    </form>
</div>
@endsection
