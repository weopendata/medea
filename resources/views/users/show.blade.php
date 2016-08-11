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

	@if (!empty($profile['expertise']) || !empty($profile['bio']))
		<h3>Over mij</h3>
		@if (!empty($profile['bio']))
		<p>
			{{ $profile['bio'] }}
		</p>
		@endif
		@if (!empty($profile['expertise']))
		<p>
			{{ $profile['expertise'] }}
		</p>
		@endif
	@endif

	@if (@$person['showContactForm'])
		<h3>Contact</h3>
		<form class="ui form" style="max-width:25em">
			<div class="field">
				<textarea placeholder="Schrijf een bericht aan {{ $profile['firstName'] }}" rows="3"></textarea>
			</div>
		</form>
	@elseif (@$person['showEmail'])
		<h3>Contact</h3>
		<p>
			Email: <a href="mailto:{{ $profile['email'] }}">{{ $profile['email'] }}</a>
		</p>
	@endif
</div>
@endsection


