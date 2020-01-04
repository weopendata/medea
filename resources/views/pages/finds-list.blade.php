@extends('main')

@section('title', 'Vondsten')

@section('content')
@endsection

@section('script')
<script type="text/javascript">
window.initialFinds = {!! json_encode($finds) !!};
window.filterState = {!! json_encode($filterState) !!};
window.fields = {!! json_encode($fields) !!};
window.link = {!! json_encode($link) !!};
</script>
@endsection

@section('component')
<finds-overview></finds-overview>
@endsection
