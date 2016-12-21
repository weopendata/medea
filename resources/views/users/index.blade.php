@extends('main')

@section('title', 'Leden')

@section('content')
<div class="ui container">
	<table class="ui celled table">
		<thead>
			<tr>
				<th>Gebruiker</th>
				<th>Vondsten</th>
			</tr>
		</thead>
		@foreach ($users as $user)
		<tr>
			@if ($user['hasPublicProfile'] || \Auth::user()->id == $user['id'] || \Auth::user()->hasRole('administrator'))
				<td><a href="/persons/{{ $user['id'] }}">{{ $user['firstName'] }} {{ $user['lastName'] }}</a></td>
			@else
				<td>{{ $user['firstName'] }} {{ $user['lastName'] }}</td>
			@endif
			<td>{{ $user['finds'] }}</td>
		</tr>
		@endforeach
	</table>
</div>
@endsection
