@extends('main')

@section('title', 'Collectie ' . $collection['identifier'])

@section('content')
@endsection

@section('component')
  <collection></collection>
@endsection

@section('script')
<script type="text/javascript">
window.initialCollection = {!! json_encode($collection) !!};
</script>
@endsection
