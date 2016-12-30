@extends('main')

@section('title', 'Vondst ' . $find['identifier'])

@section('content')
<find-event-detail v-if="find" :find="find" :user="user"></find-event-detail>

<!-- Root element of PhotoSwipe. Must have class pswp. -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="pswp__bg"></div>
	<div class="pswp__scroll-wrap">

		<!-- Container that holds slides. PhotoSwipe keeps only 3 slides in DOM to save memory. -->
		<!-- don't modify these 3 pswp__item elements, data is added later on. -->
		<div class="pswp__container">
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
			<div class="pswp__item"></div>
		</div>

		<!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->
		<div class="pswp__ui pswp__ui--hidden">

			<div class="pswp__top-bar">

				<!--  Controls are self-explanatory. Order can be changed. -->

				<div class="pswp__counter"></div>

				<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>

				<button class="pswp__button pswp__button--share" title="Share"></button>

				<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>

				<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>

				<!-- Preloader demo http://codepen.io/dimsemenov/pen/yyBWoR -->
				<!-- element will get class pswp__preloader--active when preloader is running -->
				<div class="pswp__preloader">
					<div class="pswp__preloader__icn">
						<div class="pswp__preloader__cut">
							<div class="pswp__preloader__donut"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
				<div class="pswp__share-tooltip"></div>
			</div>
			<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
			<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
			<div class="pswp__caption">
				<div class="pswp__caption__center"></div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('script')
<script type="text/javascript">
var initialFind = {!! json_encode($find) !!};
window.fields = {!! json_encode($fields) !!};
@if (!empty($publicUserInfo))
window.publicUserInfo = {!! json_encode($publicUserInfo) !!};
@endif
</script>
<link href="https://unpkg.com/select2@4.0.3/dist/css/select2.min.css" rel="stylesheet" type="text/css">
<script src="https://unpkg.com/select2@4.0.3"></script>
<script src="{{ asset('js/finds-detail.js') }}"></script>
<script src="{{ asset('assets/js/photoswipe.min.js') }}"></script>
<link href="{{ asset('assets/css/photoswipe.min.css') }}" rel="stylesheet" type="text/css">
@endsection
