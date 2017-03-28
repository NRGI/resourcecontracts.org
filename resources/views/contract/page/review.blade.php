@extends('layout.app-full')
@section('css')
	<link rel="stylesheet" href="{{ asset('css/annotator.css') }}"/>
	<link rel="stylesheet" href="{{ url('css/contract-view.css') }}">
	<link rel="stylesheet" href="{{ url('css/contract-review.css') }}">
@stop
@section('content')
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
		var app_url = '{{url()}}';
		var contractTitle = "{{$contract->title}}";
		var contractAppSetting = {
			contract_id: '{{$contract->id}}',
			total_pages: '{{$contract->pages->count()}}',
			allpage_url: "{{route('contract.allpage.get', ['id'=>$contract->id])}}",
			annotation_url: "{{route('contract.annotations', ['id'=>$contract->id])}}",
			search_url: "{{route('contract.page.search', ['id'=>$contract->id])}}",
			page_no: 1
		};
		var saveApi = "{{route('contract.page.store', ['id'=>$contract->id])}}";
		var loadApi = "{{route('contract.page.get', ['id'=>$contract->id])}}";
	</script>
	<script src="{{ asset('assets/js/review.js') }}"></script>
@stop
