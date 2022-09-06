@extends('layout.app')

@section('css')
	<link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
	<style>
		.select2 {
			margin-right: 20px !important;
			margin-top: 4px !important;
		}

		.filter-option .col-md-4{
			margin-bottom: 10px;
		}

		.filter {
			float: left;
			margin-right: 10px;
			margin-top: 7px
		}
	</style>
@stop

@section('content')
	<div class="panel panel-default">
		<div class="panel-heading">@lang('activitylog.activitylog')</div>

		<div class="panel-body">
			{!! Form::open(['route' => 'activitylog.index', 'method' => 'get', 'class'=>'form-inline']) !!}
			<div class="clearfix">
				<label class="filter">@lang('activitylog.filterby')</label>
			</div>
			<div class="row filter-option">
				<div class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('contract', ['all'=>trans('activitylog.all_contract')] + $contracts, Input::get('contract'),['class' =>'form-control']) !!}
				</div>
				<div class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('user', ['all'=>trans('activitylog.all_user')] + $users , Input::get('user') ,	['class' =>'form-control']) !!}
				</div>
				<div class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('category', ['all'=>trans('activitylog.all_category')] + $categories , Input::get('category') , ['class' =>'form-control']) !!}
				</div>
				@if(!is_null($countries))
					<div class="col-md-4 col-sm-6 col-xs-6">
						{!! Form::select('country', ['all'=>trans('activitylog.all_country')] + $countries , Input::get('country') , ['class' =>'form-control']) !!}
					</div>
				@endif
				<div class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::select('status', ['all'=>trans('activitylog.all_status')] + $status , Input::get('status') ,['class' =>'form-control']) !!}
				</div>
				<div class="col-md-4 col-sm-6 col-xs-6">
					{!! Form::submit(trans('contract.search'), ['class' => 'btn btn-primary']) !!}
				</div>
			</div>
			{!! Form::close() !!}

			<table class="table table-striped table-responsive">
				<thead>
				<tr>
					<th width="40%">
					@lang('activitylog.contract')</td>
					<th>
					@lang('activitylog.action')</td>
				</tr>
				</thead>
				<tbody>

				@forelse($activityLogs as $activitylog)
					<tr>
						<td>
							<a href="{{route('contract.show',$activitylog->contract_id)}}">{{ $activitylog->contract->metadata->contract_name ?? ''}}</a>
						</td>
						<td>
							{{ trans($activitylog->message,$activitylog->message_params) }} <br>
							@lang('global.by') {{$activitylog->user->name}} @lang('global.on')
							<?php echo $activitylog->createdDate('F d, Y \a\t h:i A');?>
						</td>
					</tr>
				@empty
					<tr>
						<td colspan="2">@lang('activitylog.not_found')</td>
					</tr>
				@endforelse
				</tbody>
			</table>
			{!!$activityLogs->appends(Input::all())->render()!!}
		</div>
	</div>
@endsection

@section('script')
	<script src="{{asset('js/select2.min.js')}}"></script>
	<script>
		var lang_select = '@lang('global.select')';
		$(function () {
			$('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
		});
	</script>
@stop

