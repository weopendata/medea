@extends('main')

@section('title', 'Collecties')

@section('content')
<div id="app">
  <collections-create></collections-create>
</div>
@endsection

@section('script')
<script type="text/javascript">
window.initialCollections = {!! json_encode($collections) !!};
window.filterState = {!! json_encode($filterState) !!};
window.fields = {!! json_encode($fields) !!};
window.link = {!! json_encode($link) !!};
</script>
<script src="{{ asset('js/collections-create.js') }}?{{ Config::get('app.version') }}"></script>
@endsection