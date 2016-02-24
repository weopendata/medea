@extends('main')

@section('content')

<div class="ui eight column centered grid">
    <div class="column">
        <form class="ui form" role="form" method="POST" action="{{ url('/login') }}">
            {!! csrf_field() !!}

            <div class="field">
                <label>Email</label>
                <input type="text" name="email" placeholder="Email">

                @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
                @endif
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" class="form-control" name="password">

                @if ($errors->has('password'))
                <span class="help-block">
                    <strong>{{ $errors->first('password') }}</strong>
                </span>
                @endif
            </div>

            <button class="ui button" type="submit">Submit</button>
        </form>
    </div>
</div>
@endsection
