@extends('main')

@section('content')
<finds-list :finds="finds" :user="user"></finds-list>
@endsection

@section('script')
<script type="text/javascript">var initialFinds = {!! json_encode($finds) !!};</script>
<script src="{{ asset('js/finds-list.js') }}"></script>
@endsection