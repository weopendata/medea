@extends('main')

@section('title', 'Leden')

@section('content')
<div class="ui container">
	<table class="ui celled table">
		<thead>
			<tr>
				<th>Gebruiker</th>
				<th>Rollen</th>
				<th>Detectorist</th>
				<th>Expert</th>
				<th>Registrator</th>
				<th>Validator</th>
			</tr>
		</thead>
		@foreach ($users as $user)         
		<tr>
			<td>{{ $user['firstName'] }} {{ $user['lastName'] }}</td>
			<td>{{ implode(' ', $user['roles']) }}</td>
		</tr>
		@endforeach
	</table>
</div>
@endsection
