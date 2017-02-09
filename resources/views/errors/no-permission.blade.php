<style>
	body {
		margin: 0;
		padding: 0;
		width: 100%;
		height: 100%;
		color: #B0BEC5;
		display: table;
		font-weight: 100;
	}

	.container {
		text-align: center;
		display: table-cell;
		vertical-align: middle;
	}

	.content {
		text-align: center;
		display: inline-block;
	}

	.title {
		font-size: 40px;
		color: #f4645f;
		margin-bottom: 40px;
	}
</style>
@extends('layout.app')

@section('content')
	<div class="container">
		<div class="content">
			<div class="title">
				You don't have sufficient permissions.
			</div>
			<a href="{{env('APP_DOMAIN')}}">Go back to home</a>
		</div>
	</div>
@endsection
@section('script')
	<link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
	<script src="{{asset('js/select2.min.js')}}"></script>
	<script type="text/javascript">
		var lang_select = '@lang('global.select')';
		$('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
	</script>
@stop