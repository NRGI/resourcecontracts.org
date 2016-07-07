@extends('layout.app')
@section('content')

	<div class="panel panel-default" xmlns="http://www.w3.org/1999/html">
		<div class="panel-heading">
			<h3>@lang('contract.utility_autorename')</h3>
		</div>
		<div class="panel-body">
			<div class="col-lg-11">
				{!! Form::open(['route'=>'utility.index', 'method' => 'GET', 'class' => 'form-inline']) !!}
				<div class="form-group">
					{!! Form::select('category', [''=>'Select Category','all' => 'All', 'rc'=>'RC','olc'=>'OLC'], \Illuminate\Support\Facades\Request::get('category'), ["class"=>"select2 required form-control"]) !!}
				</div>
				<div class="form-group">
					{!! Form::select('country' , [''=>'Select Country','all' => 'All'] + trans('codelist/country'), \Illuminate\Support\Facades\Request::get('country'), ["class"=>"select2 required form-control"]) !!}
				</div>
				{!! Form::button('Submit', ['type' => 'submit','class' => 'btn btn-primary']) !!}
				{!! Form::close() !!}
			</div>

			@if($confirm)
				@if($contracts)
					<table class="table" style="margin: 50px 0px 30px">
						<thead>
						<th>Contract Id</th>
						<th>Contracts Current Name</th>
						<th>Contracts New Name</th>
						<th>Remarks</th>
						</thead>
						<tbody>
						@foreach($contracts as &$contract)
							<tr>
								<td><a target="_blank" href="{{route('contract.show', $contract['id'])}}">{{
								$contract['id']
								}}</a></td>
								<td>{{ $contract['old'] }}</td>
								<td>{{ $contract['new'] }}</td>
								<td>
									@if($contract['old'] == $contract['new'])
										Identical, will be skip <?php unset($contracts[$contract['id']]);?>
									@endif
								</td>
							</tr>
						@endforeach

						</tbody>
					</table>
							@if($contracts)
								<div style="float: left; margin-right: 10px">
									{!! Form::open(['route'=>'utility.submit' ,'method' => 'post','class' => 'form-inline' ]) !!}
									{!! Form::hidden('contracts',json_encode($contracts)) !!}
									{!! Form::button('Rename Contract', ['type' => 'submit','class' => 'btn btn-success confirm','data-confirm'=>'Are you sure you want to rename these contracts?']) !!}
									{!! Form::close() !!}
								</div>
								<a href="{{route('utility.index')}}" class="btn btn-primary">Cancel</a>
							@else
								<a href="{{route('utility.index')}}" class="btn btn-primary">Cancel</a>
							@endif
				@endif
			@endif
		</div>
	</div>
@endsection
