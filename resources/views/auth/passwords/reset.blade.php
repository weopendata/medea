@extends('main')

@section('content')
<div style="max-width:25em;margin:0 auto;">
    <form class="card ui form" role="form" method="POST" action="{{ url('/password/reset') }}">

        {!! csrf_field() !!}

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="card-textual" style="padding-top:8px;">
            <div class="card-title">Reset wachtwoord</div>

            <div class="field{{ $errors->has('email') ? ' error' : '' }}">
                <label>Emailadres</label>
                <input type="email" name="email" value="{{ $email or old('email') }}">
                @if ($errors->has('email'))
                    <div class="ui negative message">
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>

            <div class="field{{ $errors->has('password') ? ' error' : '' }}">
                <label>Nieuw wachtwoord</label>
                <input type="password" name="password">
                @if ($errors->has('password'))
                    <div class="ui negative message">
                        {{ $errors->first('password') }}
                    </div>
                @endif
            </div>

            <div class="field{{ $errors->has('password_confirmation') ? ' error' : '' }}">
                <label>Nieuw wachtwoord herhalen</label>
                <input type="password" name="password_confirmation">
                @if ($errors->has('password_confirmation'))
                    <div class="ui negative message">
                        {{ $errors->first('password_confirmation') }}
                    </div>
                @endif
            </div>
        </div>
        <div class="card-bar">
            <button type="submit" class="btn btn-primary">
                Wachtwoord bewaren
            </button>
        </div>
    </form>
</div>
@endsection
