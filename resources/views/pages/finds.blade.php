@extends('main')

@section('content')
<finds-list :finds="finds" :user="user"></finds-list>
@endsection

@section('script')
<script src="{{ asset('js/findslist.js') }}"></script>
@endsection