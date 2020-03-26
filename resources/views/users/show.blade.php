@extends('main')

@section('title', $profile['firstName'] . ' ' . $profile['lastName'])

@section('content')
@endsection

@section('component')
<user-detail></user-detail>
@endsection

@section('script')
    @if (isset($collections))
    <script type="text/javascript">
    window.profile = {!! json_encode($profile) !!};
    window.findCount = {!! json_encode($findCount) !!};
    window.profile = {!! json_encode($profile) !!};
    window.roles = {!! json_encode($roles) !!};
    window.collections = {!! json_encode($collections) !!};
    window.profileAccessLevel = {!! json_encode($profileAccessLevel) !!};
    window.id = {!! json_encode($id) !!};
    </script>
    @endif
@endsection
