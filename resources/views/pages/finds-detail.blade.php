@extends('main')

@section('content')
<find-event-detail v-if="find" :find="find" :user="user"></find-event-detail>
@endsection

@section('script')
<script src="{{ asset('js/finds-detail.js') }}"></script>
@endsection
