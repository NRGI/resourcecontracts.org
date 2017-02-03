<div id="role-form" class="modal fade title" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">{{ trans('user.add_role') }}</h4>
			</div>
			<div class="modal-body col-sm-12">
				<h5>{{ trans('user.name') }}</h5>
				{!! Form::text('name', null, ['class' => 'name form-control']) !!}
				<h5>{{ trans('user.description') }}</h5>
				{!! Form::text('description', null, ['class' => 'description form-control']) !!}
				<h5>{{ trans('user.permission') }}</h5>
				<div class="row permissions">
					@foreach($permissions as $key => $value)
						<label class="col-sm-4"><input type="checkbox" class="permission"
							name="permissions[]"
							id="{{$key}}" value="{{$key}}">
							{{$value}}</label>
					@endforeach
				</div>
				{!! Form::hidden('role-id', null, ['id' => 'role-id']) !!}
			</div>
			<div class="modal-footer clearfix">
				<button type="button" class="btn btn-default btn-warning" data-dismiss="modal">{{ trans
							('user.cancel') }}</button>
				<button type="submit" id="add-role" class="btn btn-default btn-success">{{ trans('user.save')
				}}</button>
			</div>
		</div>
	</div>
</div>
