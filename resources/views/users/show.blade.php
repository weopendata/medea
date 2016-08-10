@extends('main')

@section('title', $profile->firstName + ' ' + $profile->lastName)

@section('content')
<div class="ui container">
qsd {{$profile->getProperties() + ' ' + $profile->lastName}}
@endsection
