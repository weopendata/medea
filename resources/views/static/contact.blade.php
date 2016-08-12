@extends('main')

@section('title', 'Contact')

@section('nav')
<style type="text/css">
.contact-frame {
	width: 100%;
	min-height: 100vh;
}
.nav-contact {
	height: 0;
}
.nav-contact	.ui.menu .item {
	color: white !important;
}
.nav-contact	.ui.menu .active.item {
	border-color: white !important;
}
</style>
<div class="nav-contact">
@endsection

@section('content')
<iframe class="contact-frame" src="https://docs.google.com/forms/d/e/1FAIpQLSfkdy68ZBrkiCw2nMVf8nwzcohzT3bfi2rmabnSAGzec0r8DA/viewform?embedded=true" width="760" height="500" frameborder="0" marginheight="0" marginwidth="0">Bezig met laden...</iframe>
@endsection
