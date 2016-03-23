@extends('main')

@section('title', 'Vondst')

@section('content')
<find-event-detail v-if="find" :find="find" :user="user"></find-event-detail>
@endsection

@section('script')
<script type="text/javascript">var initialFind = {!! json_encode($find) !!};</script>
<script src="{{ asset('js/finds-detail.js') }}"></script>
@endsection
