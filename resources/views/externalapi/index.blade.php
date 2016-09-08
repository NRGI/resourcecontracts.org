@extends('layout.app')
@section('content')

	<div class="panel panel-default" xmlns="http://www.w3.org/1999/html">
		<div class="panel-heading">
			<h3>@lang('ea.external_api')</h3>
		</div>
		<div class="panel-body">
			<div class="col-lg-11">
				{!! Form::open(['route'=>'external-api.store', 'method' => 'POST', 'class' => 'form-inline']) !!}
				<div class="row" style=" margin-bottom: 20px;
				background-color: #F9F9F9;
				padding-top: 10px;
				border: 1px solid #DEDEDE;">
					<div class="col-md-3 form-group @if($errors->has('site')) has-error @endif">
						<label>@lang('ea.site'):</label>
						{!! Form::text('site' , null, ["class"=>"required form-control"]) !!}
						<span class="help-block">{{$errors->first('site')}}</span>
					</div>
					<div class="col-md-3 form-group  @if($errors->has('url')) has-error @endif">
						<label>@lang('ea.api_url'):</label>
						{!! Form::text('url', null, ["class"=>"required form-control"]) !!}
						<span class="help-block">{{$errors->first('url')}}</span>
					</div>
					<div class="col-md-2 form-group" style="padding-top: 25px;">
						{!! Form::button(trans('global.form.add'), ['type' => 'submit','class' => 'pull-left btn btn-primary']) !!}
					</div>
				</div>


				{!! Form::close() !!}
			</div>

			@if(!empty($apis))
				<table class="table" style="margin: 50px 0px 30px">
					<thead>
					<tr>
						<th>@lang('ea.site')</th>
						<th>@lang('ea.api_url')</th>
						<th>@lang('ea.last_updated_date')</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					@foreach($apis as $api)
						<tr>
							<td>{{$api->site}}</td>
							<td><a target="_blank" href="{{$api->url}}">{{$api->url}}</a></td>
							<td>
								@if($api->last_index_date !='')
									{{$api->last_index_date->format('M d, Y \a\t h:i A')}}
								@else
									-
								@endif
							</td>
							<td>
								{!!Form::open(['route'=>['external-api.destroy'], 'style'=>"display:inline", 'method'=>'delete'])!!}
								{!! Form::hidden('id', $api->id) !!}
								{!!Form::button(trans('global.form.delete'), ['type'=>'submit', 'class'=>'btn btn-danger confirm',	'data-confirm'=>trans('ea.confirm_api_delete')])!!}
								{!!Form::close()!!}

								{!!Form::open(['route'=>['external-api.remove'], 'style'=>"display:inline", 'method'=>'delete'])!!}
								{!! Form::hidden('id', $api->id) !!}
								{!!Form::button(trans('ea.remove_all'), ['type'=>'submit', 'class'=>'btn btn-danger
								confirm',	'data-confirm'=>trans('ea.confirm_index_remove')])!!}
								{!!Form::close()!!}

								{!!Form::open(['route'=>['external-api.update'], 'style'=>"display:inline", 'method'=>'post'])!!}
								{!! Form::hidden('id', $api->id) !!}
								{!!Form::button(trans('ea.update'), ['type'=>'submit', 'class'=>'btn btn-success confirm',	'data-confirm'=>trans('ea.confirm_update')])!!}
								{!!Form::close()!!}

								{!!Form::open(['route'=>['external-api.indexAll'], 'style'=>"display:inline", 'method'=>'post'])!!}
								{!! Form::hidden('id', $api->id) !!}
								{!!Form::button(trans('ea.index_all'), ['type'=>'submit', 'class'=>'btn	btn-primary confirm',	'data-confirm'=>trans('ea.confirm_index_all')])!!}
								{!!Form::close()!!}
							</td>
						</tr>
					@endforeach
					</tbody>
				</table>
			@endif
		</div>
	</div>
@endsection
