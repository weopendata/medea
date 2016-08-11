@extends('main')

@section('title', $profile['firstName'] + ' ' + $profile['lastName'])

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

	@if (!empty($profile['expertise']) || !empty($profile['bio']) || !empty($profile['research']))
		<h3>Over mij</h3>
		@if (!empty($profile['research']))
		<p>
			<b>Onderzoek</b>: {{ nl2br($profile['research']) }}
		</p>
		@endif
		@if (!empty($profile['bio']))
		<p>
			<b>Bio</b>: {{ nl2br($profile['bio']) }}
		</p>
		@endif
		@if (!empty($profile['expertise']))
		<p>
			<b>Expertise</b>: {{ nl2br($profile['expertise']) }}
		</p>
		@endif
	@endif

	@if ($profile['showContactForm'] || $profile['showEmail'])
		<h3>Contact</h3>
		@if (@$profile['showEmail'])
			<p>
				Email: <a href="mailto:{{ $profile['email'] }}">{{ $profile['email'] }}</a>
			</p>
		@endif
		@if (@$profile['showContactForm'])
			<form class="ui form" style="max-width:25em">
				<div class="field">
					<label>Bericht aan {{ $profile['firstName'] }}</label>
					<textarea rows="3"></textarea>
				</div>
				<div class="field">
					<button type="submit" class="ui small blue button">Verzenden</button>
				</div>
			</form>
		@endif
	@endif
</div>

@if (Auth::user()->hasRole('administrator'))
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p>&nbsp;</p>
	<p style="float:right">
		<a href="/settings/{{$id}}" class="ui blue button">Aanpassen</a>
	</p>
	<h3>Alleen voor administrator:</h3>
	<pre style="padding: 1em;">{!! json_encode($profile, JSON_PRETTY_PRINT) !!}</pre>
@endif
@endsection


