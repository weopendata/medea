@extends('main')

@section('content')

<div class="ui grid doubling ">
<div class="doubling four column row">
    <div class="centered column">
        <form class="ui form" role="form" method="POST" action="{{ url('/login') }}">
            {!! csrf_field() !!}

            <div class="field">
                <label>Email</label>
                <input type="text" name="email" placeholder="Email">

                @if ($errors->has('email'))
                <div class="ui negative message">
                    <p>{{ $errors->first('email') }}</p>
                </div>
                @endif
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" class="form-control" name="password">

                @if ($errors->has('password'))
                <div class="ui negative message">
                    <p>{{ $errors->first('password') }}</p>
                </div>
                @endif
            </div>

            <button class="ui button" type="submit">Submit</button>
        </form>
    </div>
</div>
</div>
@endsection
