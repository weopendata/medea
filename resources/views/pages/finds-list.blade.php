@extends('main')

@section('title', 'Vondsten')

@section('content')
<div class="ui container">
  <finds-filter :model.sync="filterState"></finds-filter>
	<finds-list :finds="finds" :user="user"></finds-list>
</div>
@endsection

@section('script')
<script type="text/javascript">
window.initialFinds = {!! json_encode($finds) !!};
window.filterState = {!! json_encode($filterState) !!};
</script>
<script src="{{ asset('js/finds-list.js') }}"></script>
@endsection