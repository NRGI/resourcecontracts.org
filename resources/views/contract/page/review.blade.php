@extends('layout.app-full')
@section('css')
	<link rel="stylesheet" href="{{ asset('css/annotator.css') }}"/>
	<link rel="stylesheet" href="{{ url('css/contract-view.css') }}">
	<link rel="stylesheet" href="{{ url('css/contract-review.css') }}">
@stop
@section('content')
	<div style="padding: 6px 12px; background: #f5f5f5; border-bottom: 1px solid #ddd;">
		<select id="translation-variant-select" class="form-control" style="display:inline-block; width:auto;">
			<option value="" {{ request('lang') == '' ? 'selected' : '' }}>@lang('contract.original_text')</option>
			<option value="en" {{ request('lang') == 'en' ? 'selected' : '' }}>English</option>
			<option value="es" {{ request('lang') == 'es' ? 'selected' : '' }}>Spanish</option>
			<option value="fr" {{ request('lang') == 'fr' ? 'selected' : '' }}>French</option>
		</select>
	</div>
	<div id="content"></div>
@endsection
@section('script')
	<script>
		var debug = function () {
			var DEBUG = false;
			if (DEBUG) {
				console.log("-----");
				for (var i = 0; i < arguments.length; i++) {
					console.log(arguments[i]);
				}
			}
		}
		var LANG = {!! json_encode(trans('annotation'))!!};
		var back_url = '{!!$back!!}';
		var app_url = '{{url()->to("/")}}';
		var contractTitle = "{{$contract->title}}";
		var contractAppSetting = {
			contract_id: '{{$contract->id}}',
			total_pages: '{{$contract->pages->count()}}',
			allpage_url: "{{route('contract.allpage.get', ['id'=>$contract->id])}}",
			annotation_url: "{{route('contract.annotations', ['contractId'=>$contract->id])}}",
			search_url: "{{route('contract.page.search', ['id'=>$contract->id])}}",
			page_no: 1
		};
		var TRANSLATION_LANG = {!!json_encode($translationLang)!!};
		var saveApi = "{{route('contract.page.store', ['id'=>$contract->id])}}";
		var translationVariant = "{{ request('lang', '') }}";
		var loadApi = "{{route('contract.page.get', ['id'=>$contract->id])}}" + (translationVariant ? "?lang=" + translationVariant : "");
		var publishApi = "{{route('contract.page.publish',['id'=>$contract->id])}}";
	</script>
	<script src="{{ asset('assets/js/review.js') }}"></script>
	<script>
		document.getElementById('translation-variant-select').addEventListener('change', function () {
			var lang = this.value;
			var url  = new URL(window.location.href);
			if (lang) {
				url.searchParams.set('lang', lang);
			} else {
				url.searchParams.delete('lang');
			}
			window.location.href = url.toString();
		});
	</script>
@stop
