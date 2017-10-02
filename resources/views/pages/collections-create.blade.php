@extends('main')

@section('title', 'Collecties')

@section('content')
<div id="app">
  <collections-create></collections-create>
</div>
@endsection

@section('script')
<script type="text/javascript">
@if (isset($collection))
window.initialCollection = {!! json_encode($collection) !!};
@endif
window.fields = {!! json_encode($fields ?? [
  'collectionType' => [
    'prive collectie',
    'heemkundige collectie',
    'museumcollectie',
    'bibliotheekcollectie',
    'archiefcollectie',
  ]
]) !!};
</script>
<script src="{{ asset('js/collections-create.js') }}?{{ Config::get('app.version') }}"></script>
@endsection
