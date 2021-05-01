@extends('main')

@section('title', 'Typologie Browser')

@section('content')
@endsection

@section('component')
    <typology-browser></typology-browser>
@endsection

@section('script')
    <script type="text/javascript">
        window.typologyTree = {!! json_encode($typologyTree) !!};
    </script>
@endsection
