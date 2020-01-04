@extends('main')

@section('title', 'Leden')

@section('content')
@endsection

@section('component')
<users-overview></users-overview>
@endsection

@section('script')
<script type="text/javascript">
window.users = {!! json_encode($users) !!};
window.paging = {!! json_encode($paging) !!};
window.stats = {!! json_encode($stats) !!};
window.sortBy = {!! json_encode($sortBy) !!};
window.sortOrder = {!! json_encode($sortOrder) !!};
</script>
@endsection
