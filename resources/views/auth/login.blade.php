@extends('main')

@section('content')

    <div style="max-width:45em;margin:0 auto;">
        <div style="width:20em;float:left;padding:16px">
            <form class="ui form" role="form" method="POST" action="{{ url('/login') }}">
                <h2>Aanmelden</h2>
                {!! csrf_field() !!}

                <div class="field">
                    <label>Emailadres</label>
                    <input type="text" name="email">

                    @if ($errors->has('email'))
                        <div class="ui negative message">
                            <p>{{ $errors->first('email') }}</p>
                        </div>
                    @endif
                </div>

                <div class="field">
                    <label>Wachtwoord</label>
                    <input type="password" class="form-control" name="password">

                    @if ($errors->has('password'))
                        <div class="ui negative message">
                            <p>{{ $errors->first('password') }}</p>
                        </div>
                    @endif
                </div>

                <button class="ui green button" type="submit">Aanmelden</button>

                <p>
                    <a href="/password/reset">Wachtwoord vergeten?</a>
                </p>
            </form>
        </div>
        @if (!env('APP_PUBLIC_ONLY', false))
            <div style="max-width:20em;float:right;padding:16px">
                <h2 class="status-lg">
                    Eerste keer?
                </h2>
                <p>
                    Je moet eerst <a href="/#register">registreren</a> vooraleer je kunt aanmelden.
                </p>
                <p>
                    <a href="/#register" class="ui blue button">Nu registereren</a>
                </p>
            </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
      window.startIntro = function () {
        introJs().start()
      }
      if (window.location.href.indexOf('startIntro') !== -1) {
        window.startIntro()
      }
    </script>
@endsection
