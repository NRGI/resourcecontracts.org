@extends('layout.app')

@section('content')
	<div class="panel panel-default">
		<div class="panel-heading">{{ trans('user.role') }}
			<a href="#" type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#role-form">
				{{ trans('user.add_role') }}
			</a>
		</div>

		<div class="panel-body">
			<table class="table table-striped table-responsive">
				<thead>
				<tr>
					<th>{{ trans('user.id') }}</th>
					<th>{{ trans('user.name') }}</th>
					<th>{{ trans('user.display_name') }}</th>
					<th>{{ trans('user.description') }}</th>
					<th>{{ trans('user.permission') }}</th>
					<th>{{ trans('user.created_on') }}</th>
					<th width="140px">{{ trans('user.action') }}</th>
				</tr>
				</thead>

				@forelse($roles as $role)
					<tr>
						<td>{{$role->id}}</td>
						<td>{{$role->name}}</td>
						<td>{{$role->display_name}}</td>
						<td>{{$role->description}}</td>
						<td> {{$role->perms->implode('display_name', ', ')}}
						</td>
						<td>{{$role->created_at}}</td>
						<td>
							@if($role->name !== "superadmin")
								<a>
									<button type="button" class="btn btn-primary" data-toggle="modal"
											data-target="#role-form" data-id="{{$role->id}}"
											data-display_name="{{$role->display_name}}"
											data-description="{{$role->description}}"
											data-permissions="{{$role->perms}}">
										{{ trans('user.edit') }}
									</button>
								</a>

								{!!Form::open(['route'=>['role.destroy', $role->id], 'style'=>"display:inline",
								'method'=>'delete'])!!}
									{!!Form::button(trans('user.delete'), ['type'=>'submit',
									'id'=>"{{$role->id}}",
									'class'=>'btn btn-danger confirm',
									'data-confirm'=>trans('user.confirm_text_role_delete')])!!}
								{!!Form::close()!!}
							@else
								-
							@endif
						</td>

					</tr>
				@empty
					<tr>
						<td colspan="7">@lang("user.role_not_found")</td>
					</tr>
				@endforelse
			</table>
			{!! Form::open(['route' => ['role.update'], 'method' => 'PATCH', 'class'=>'role-form form-horizontal'])!!}
				@include('user.role.form', ['action' => 'edit'])
			{!! Form::close() !!}
		</div>
	</div>
@endsection
@section('script')
	<script>
		$('#role-form').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var id = button.data('id');
			if(typeof id == 'undefined'){
				$('.modal-title').html('Add Role');
				return;
			}else{
				$('.modal-title').html('Edit Role');
			}
			var display_name = button.data('display_name');
			var description = button.data('description');
			var permissions = button.data('permissions');
			var modal = $(this);
			modal.find('.name').val(display_name);
			modal.find('.description').val(description);
			modal.find('#role-id').val(id);
			$.each(permissions, function (key, value) {
					$().attr('checked', 'checked');
				$('#' + value.id)[0].checked =true;

			});
		});

		$('#role-form').on('hidden.bs.modal', function () {
			var modal = $(this);
			modal.find('.name').val('');
			modal.find('.description').val('');
			modal.find('input.permission').each(function(){
				$(this)[0].checked =false;
			});
		});

	</script>
	<script src="{{asset('js/role-ajax.js')}}"></script>
@stop
