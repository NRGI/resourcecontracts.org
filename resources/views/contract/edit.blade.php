@extends('layout.app')

@section('content')
	@include('contract.partials.form.language', ['view'=>'edit'])

	<div class="panel panel-default">
		<div class="panel-heading"> Editing
			<span>{{$contract->metadata->contract_name ? $contract->metadata->contract_name : $contract->metadata->project_title}}</span></div>

		<div class="panel-body contract-wrapper">
			@if (count($errors) > 0)
				<div class="alert alert-danger">
					<strong>@lang('contract.whoops')</strong> @lang('contract.problem')<br><br>
					<ul>
						@foreach ($errors->all() as $error)
							<li>{!! $error !!}</li>
						@endforeach
					</ul>
				</div>
			@endif
			{!! Form::model($contract,['route' => array('contract.update', $contract->id) ,  'class'=>'form-horizontal contract-form', 'method'=>'PATCH',
			'files' => true]) !!}
			{!! Form::hidden('contract_id', $contract->id)!!}
			@include('contract.form', ['action'=>'edit', 'contact' => $contract])
			{!! Form::close() !!}
		</div>
	</div>
@stop