@extends('main')

@section('title', 'Collecties')

@section('content')
@endsection

@section('component')
<collections-list></collections-list>
@endsection

@section('script')
  <script type="text/javascript">
    window.initialCollections = {!! json_encode($collections) !!}
    window.filterState = {!! json_encode($filterState) !!}
    window.fields = {!! json_encode($fields) !!}
    window.link = {!! json_encode($link) !!}
  </script>
@endsection
