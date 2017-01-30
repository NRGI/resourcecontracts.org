@section('css')
	<link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
@stop
<div class="form-group">
	<label class="col-md-4 control-label">{{ trans('user.name') }} <span class="red">*</span></label>

	<div class="col-md-6">
		{!! Form::text('name', null, ['class' => 'form-control']) !!}
	</div>
</div>

<div class="form-group">
	<label class="col-md-4 control-label">{{ trans('user.email') }} <span class="red">*</span></label>

	<div class="col-md-6">
		{!! Form::email('email', null, ['class' => 'form-control']) !!}
	</div>
</div>

<div class="form-group">
	<label class="col-md-4 control-label">{{ trans('user.password') }} <span class="red">*</span></label>

	<div class="col-md-6">
		{!! Form::password('password', ['class' => 'form-control']) !!}
	</div>
</div>

<div class="form-group">
	<label class="col-md-4 control-label">{{ trans('user.confirm_password') }} <span class="red">*</span></label>

	<div class="col-md-6">
		{!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
	</div>
</div>


<div class="form-group">
	<label class="col-md-4 control-label">{{ trans('user.organization') }}</label>

	<div class="col-md-6">
		{!! Form::text('organization', null, ['class' => 'form-control']) !!}
	</div>
</div>

<div class="form-group">
	<label class="col-md-4 control-label">{{ trans('user.role') }} <span class="red">*</span></label>
	<?php
	$old = null;
	if ($action == 'update') {
		$role = $user->roles->toArray();
		$old  = isset($role[0]['name']) ? $role[0]['name'] : null;
	}
	?>
	<div class="col-md-6">
		<div class="col-md-5">
			{!! Form::select('role', ['' => 'Select'] + $roles, $old, ['class' => 'form-control role'])!!}
		</div>
		<div class="col-md-1">
			<p>
				<button type="button" class="btn btn-info" data-toggle="modal" data-target="#add-role-form">
					{{ trans('user.add_role') }}
				</button>
			</p>

		</div>
	</div>
</div>

<div class="form-group country"
	 style="display: @if(in_array(old('role'), config("nrgi.country_role")) or in_array($old, config("nrgi.country_role")) or $current_user->hasRole(config("nrgi.country_role"))) block @else none @endif">
	<label for="country" class="col-md-4 control-label">@lang('contract.country')<span class="red">*</span></label>
	<div class="col-sm-6">
		{!! Form::select('country[]', ['' => 'select'] + $country ,
		isset($user->country)?$user->country:null, ["class"=>"required form-control"])!!}
	</div>
</div>


<div class="form-group">
	<label class="col-md-4 control-label">{{ trans('user.status') }}</label>

	<div class="col-md-6">
		<label>
			{!! Form::radio('status', 'true', null) !!}
			{{ trans('user.active') }}
		</label>
		<label>
			{!! Form::radio('status', 'false', 'null') !!}
			{{ trans('user.inactive') }}
		</label>
	</div>
</div>

<div class="form-group">
	<div class="col-md-6 col-md-offset-4">
		<button type="submit" class="btn btn-primary">
			{{ trans('user.submit') }}
		</button>
	</div>
</div>
@section('script')
	<script src="{{asset('js/select2.min.js')}}"></script>
	<script>
		var lang_select = '@lang('global.select')';
		$(function () {
			$('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
			$('.role').on("change", function () {
				var countryRoles ={!! json_encode(config("nrgi.country_role")) !!};
				var role = $(this).val();
				if (countryRoles.indexOf(role) != -1) {
					$('.country').show();
				} else {
					$('.country').hide();
				}
			});

			$('.role-form').on('submit', function (e) {
				e.preventDefault();
				var form = $(this);
				var url = form.prop('action');
//				form.find('.btn').attr('disabled', true);
				var data = form.serialize();
				form.find('.error').remove();
				$.ajax({
					url: url,
					type: 'POST',
					data: data,
					dataType: 'JSON'
				}).error(function (err) {
					var errors = JSON.parse(err.responseText);
					$.each(errors, function(k, v){
						form.find('.'+k).after('<span class="error">'+v[0]+'</span>');
					});
				}).success(function (res) {
					console.log("Success " + res);
					$('#add-role-form').modal('hide');
				}).complete(function () {
					form.find('.btn').removeAttr('disabled');
				})
			});
		});
	</script>
@stop