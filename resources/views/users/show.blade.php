@extends('main')

@section('title', $profile['firstName'] . ' ' . $profile['lastName'])

@section('content')
<div class="ui container">
    <h1>
        {{ $profile['firstName'] }} {{ $profile['lastName'] }}
        @if (in_array('detectorist', $roles))
        <small>
            Metaaldetectorist: {{ $findCount }} vondst{{ $findCount == 1 ? '' : 'en' }}
        </small>
        @endif
    </h1>
    <p>
        Lid van MEDEA sinds {{ $profile['created_at'] }}
    </p>

    @if (!empty($profile['expertise']) || !empty($profile['bio']) || !empty($profile['research']) || !empty($profile['function']) || !empty($profile['affiliation']))
        <h3>Over mij</h3>
        @if (!empty($profile['function']))
        <p>
            <b>Functie</b>: {!! nl2br(e($profile['function'])) !!}
        </p>
        @endif
        @if (!empty($profile['affiliation']))
        <p>
            <b>Instelling</b>: {!! nl2br(e($profile['affiliation'])) !!}
        </p>
        @endif
        @if (!empty($profile['research']))
        <p>
            <b>Onderzoek</b>: {!! nl2br(e($profile['research'])) !!}
        </p>
        @endif
        @if (!empty($profile['bio']))
        <p>
            <b>Bio</b>: {!! nl2br(e($profile['bio'])) !!}
        </p>
        @endif
        @if (!empty($profile['expertise']))
        <p>
            <b>Expertise</b>: {!! nl2br(e($profile['expertise'])) !!}
        </p>
        @endif
    @endif

    @if (count($errors) > 0 && $errors->has('message'))
    <div class="alert alert-danger">
       <div class="ui negative message">
            <i class="close icon"></i>
            <p>{{ $errors->first('message') }}</p>
        </div>
    </div>
    @endif

    @if (! empty(session('message')))
        <div class="ui positive message">
            <i class="close icon"></i>
            <p>{!! nl2br(e(session('message'))) !!}</p>
        </div>
    @endif

        <h3>Contact</h3>
        @if (@$profile['showEmail'])
            <p>
                Email: <a href="mailto:{{ $profile['email'] }}">{{ $profile['email'] }}</a>
            </p>
        @else
            <form action="/api/sendMessage" method="POST" class="ui form" style="max-width:25em">
                <input type="hidden" name="user_id" value="{{ $id }}">
                <div class="field">
                    <label>Bericht aan {{ $profile['firstName'] }}</label>
                    <textarea rows="3" name="message"></textarea>
                </div>
                <div class="field">
                    <button type="submit" class="ui small blue button">Verzenden</button>
                </div>
            </form>
        @endif

    <h3>Rollen:</h3>
    <ul>
        @foreach($roles as $role)
            <li>{{ $role }}</li>
        @endforeach
    </ul>
</div>
@endsection


