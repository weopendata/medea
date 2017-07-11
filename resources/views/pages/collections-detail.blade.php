@extends('main')

@section('title', 'Collectie ' . $collection['identifier'])

@section('content')
<div class="ui container">
  <collection v-if="collection" :collection="collection"></collection>
</div>
@endsection

@section('script')
<script type="text/javascript">
window.initialCollection = {!! json_encode($collection) !!};
</script>
<script src="{{ asset('js/collections-detail.js') }}?{{ Config::get('app.version') }}"></script>
@endsection
