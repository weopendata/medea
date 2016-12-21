@extends('main')

@section('title', 'Leden')

@section('content')
<div class="ui container">
<h3>Totaal aantal vondsten: {{ $stats['finds'] }}</h3>
<ul>
	<li>Gevalideerde vondsten: {{ $stats['validatedFinds'] }}
	<li>Aantal classificaties: {{ $stats['classifications'] }}
</ul>

<h3>Download vondsten</h3>
<a class="ui button" href="/api/export">Download</a>
	<table class="ui unstackable table" style="text-align:center;min-width:600px;width:100%">
		<thead>
			<tr>
				<th width="50">Actief</th>
				<th width="200" style="text-align:left">Gebruiker</th>
				<th>Detectorist</th>
				<th>Vondstexpert</th>
				<th>Registrator</th>
				<th>Validator</th>
				<th>Onderzoeker</th>
				<th>Administrator</th>
			</tr>
		</thead>
		<tr is="TrUser" v-for="user in users" :user="user"></tr>
	</table>
</div>
@endsection

@section('script')
<script type="text/javascript">
window.users = {!! json_encode($users) !!};
</script>
<script src="{{ asset('js/users-admin.js') }}"></script>
@endsection
