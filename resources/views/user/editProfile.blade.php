@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">Edit Your Profile : <i style = "color: #036;">{{$userDetails->name}}</i></div>
        <div class="panel-body">

            @if ($errors->any())
                <ul class="alert alert-danger">

                    @foreach($errors->all() as $error)
                        <li> {{ $error }}</li>
                    @endforeach

                </ul>

            @endif

                {!! Form::model($userDetails, ['route' => ['profile.update', $userDetails->id], 'method' => 'PATCH',
                 'class'=>'form-horizontal']) !!}



            <div class="form-group">
                {!! Form::label('name', trans('user.name') , ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-7">
                {!! Form::text('name', NULL , ['class' => 'form-control']) !!}
                    </div>
            </div>

            <div class="form-group">
                {!! Form::label('organization', trans('user.organization') , ['class' => 'col-sm-2 control-label'])  !!}
                <div class="col-sm-7">
                {!! Form::text('organization', NULL , ['class' => 'form-control']) !!}
                    </div>
            </div>

                <div class="form-group">
                    {!! Form::label('password', trans('user.new_password') , ['class' => 'col-sm-2 control-label'] ) !!}
                    <div class="col-sm-7">
                    {!! Form::password('password',['class' => 'form-control' , 'autocomplete' => 'off']) !!}
                        </div>
                </div>

                <div class="form-group">
                    {!! Form::label('password_confirmation', trans('user.confirm_password') , ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-7">
                    {!! Form::password('password_confirmation',['class' => 'form-control' , 'autocomplete' => 'off']) !!}
                        </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-7 col-lg-offset-2">
                {!! Form::submit(trans('user.update_your_profile') , ['class' => 'btn btn-lg pull-right btn-primary']) !!}
                        <div>
            </div>

    {!! Form::close() !!}

@stop
