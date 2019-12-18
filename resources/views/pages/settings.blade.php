@extends('main')

@section('title', 'Instellingen')

@section('content')
@endsection

@section('component')
<user-settings></user-settings>
@endsection

@section('script')
<script type="text/javascript">
window.user = {!! json_encode($user) !!};
window.roles = {!! json_encode($roles) !!};
window.accessLevels = {!! json_encode($accessLevels) !!};
</script>
@endsection
