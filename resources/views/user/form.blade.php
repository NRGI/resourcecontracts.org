@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
@stop
<div class="form-group">
    <label class="col-md-4 control-label">Name <span class="red">*</span></label>

    <div class="col-md-6">
        {!! Form::text('name', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">E-Mail Address <span class="red">*</span></label>

    <div class="col-md-6">
        {!! Form::email('email', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">Password <span class="red">*</span></label>

    <div class="col-md-6">
        {!! Form::password('password', ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">Confirm Password <span class="red">*</span></label>

    <div class="col-md-6">
        {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
    </div>
</div>


<div class="form-group">
    <label class="col-md-4 control-label">Organization</label>

    <div class="col-md-6">
        {!! Form::text('organization', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="form-group">
    <label class="col-md-4 control-label">Role <span class="red">*</span></label>
    <?php
    $old = null;
    if ($action == 'update') {
        $role = $user->roles->toArray();
        $old  = isset($role[0]['name']) ? $role[0]['name'] : null;
    }
    ?>
    <div class="col-md-6">
        {!! Form::select('role', ['' => 'Select'] + $roles, $old, ['class' => 'form-control role'])!!}
    </div>
</div>

<div class="form-group country" style="display: @if(in_array(old('role'), config("nrgi.country_role")) or in_array($old, config("nrgi.country_role")) or $current_user->hasRole(config("nrgi.country_role"))) block @else none @endif" >
    <label for="country" class="col-md-4 control-label">@lang('contract.country')<span class="red">*</span></label>
    <div class="col-sm-6">
        {!! Form::select('country[]', ['' => 'select'] + $country ,
        isset($user->country)?$user->country:null, ["class"=>"required form-control"])!!}
    </div>
</div>


<div class="form-group">
    <label class="col-md-4 control-label">Status</label>

    <div class="col-md-6">
        <label>
            {!! Form::radio('status', 'true', null) !!}
            Active
        </label>
        <label>
            {!! Form::radio('status', 'false', 'null') !!}
            InActive
        </label>
    </div>
</div>

<div class="form-group">
    <div class="col-md-6 col-md-offset-4">
        <button type="submit" class="btn btn-primary">
            Submit
        </button>
    </div>
</div>
@section('script')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script>
        $(function(){
            $('select').select2({placeholder: "Select", allowClear: true, theme: "classic"});
            $('.role').on("change",function() {
                var countryRoles ={!! json_encode(config("nrgi.country_role")) !!};
                var role = $(this).val();
                if(countryRoles.indexOf(role)!=-1){
                    $('.country').show();
                }else{
                    $('.country').hide();
                }
            });
        })

    </script>
@endsection