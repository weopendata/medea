@extends('main')

@section('title', 'Vondsten')

@section('content')
@endsection

@section('script')
    <script type="text/javascript">
      window.initialFinds = {!! json_encode($finds) !!};
      window.initialFacets = {!! json_encode($facets) !!};
      window.excludedFacets = {!! json_encode($excludedFacets) !!}
      window.filterState = {!! json_encode($filterState) !!};
      window.viewState = {!! json_encode($viewState) !!};
      window.fields = {!! json_encode($fields) !!};
      //window.link = {!! json_encode(@$link) !!};
    </script>
@endsection

@section('component')
    <finds-overview></finds-overview>
@endsection
