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
			<td><a href="/persons/{{ $user['id'] }}">{{ $user['firstName'] }} {{ $user['lastName'] }}</a></td>
			<td>{{ $user['finds'] }}</td>
		</tr>
		@endforeach
	</table>
</div>
@endsection
