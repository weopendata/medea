@extends('main')

@section('content')
<finds-list :finds="finds" :user="user"></finds-list>
@endsection

@section('script')
<script src="{{ asset('js/finds-list.js') }}"></script>
@endsection