@extends('main')

@section('title', 'Collecties')

@section('content')
@endsection

@section('component')
  <collections-create></collections-create>
@endsection


@section('script')
<script type="text/javascript">
@if (isset($collection))
window.initialCollection = {!! json_encode($collection) !!};
@endif
window.fields = {!! json_encode($fields ?? [
  'collectionType' => [
    'Fysieke collectie van instelling of vereniging',
    'Gecentraliseerde registratie van detectievondsten',
    'Kortstondig registratieproject',
  ]
]) !!};
</script>
@endsection

