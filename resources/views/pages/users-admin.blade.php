@extends('main')

@section('title', 'Leden')

@section('content')
<div class="ui container">
	<table class="ui celled table" style="table-layout:fixed">
		<thead>
			<tr>
				<th width="200">Gebruiker</th>
				<th>Detectorist</th>
				<th>Expert</th>
				<th>Registrator</th>
				<th>Validator</th>
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
