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
    <label class="col-md-4 control-label">Role</label>

    <?php
    $old = null;
    if ($action == 'update') {
        $role = $user->roles->toArray();
        $old  = isset($role[0]['id']) ? $role[0]['id'] : null;
    }
    ?>

    <div class="col-md-6">
        {!! Form::select('role', ['' => 'Select'] + $roles, $old, ['class' => 'form-control'])!!}
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