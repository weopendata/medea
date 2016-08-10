@extends('main')

@section('title', $profile['firstName'] + ' ' + $profile['lastName'])

@section('content')
<div class="ui container">
{{ json_encode($profile)}}
@endsection
