@extends('main')

@section('title', 'Vondsten')

@section('content')
<div class="ui container">
<finds-list :finds="finds" :user="user"></finds-list>
</div>
@endsection

@section('script')
<script type="text/javascript">var initialFinds = {!! json_encode($finds) !!};</script>
<script src="{{ asset('js/finds-list.js') }}"></script>
@endsection