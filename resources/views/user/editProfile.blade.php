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
                {!! Form::label('name', 'Name:' , ['class' => 'col-sm-2 control-label']) !!}
                <div class="col-sm-7">
                {!! Form::text('name', NULL , ['class' => 'form-control']) !!}
                    </div>
            </div>

            <div class="form-group">
                {!! Form::label('organization', 'Organization:' , ['class' => 'col-sm-2 control-label'])  !!}
                <div class="col-sm-7">
                {!! Form::text('organization', NULL , ['class' => 'form-control']) !!}
                    </div>
            </div>

                <div class="form-group">
                    {!! Form::label('password', 'New Password:' , ['class' => 'col-sm-2 control-label'] ) !!}
                    <div class="col-sm-7">
                    {!! Form::password('password',['class' => 'form-control' , 'autocomplete' => 'off']) !!}
                        </div>
                </div>

                <div class="form-group">
                    {!! Form::label('password_confirmation', 'Confirm Password:' , ['class' => 'col-sm-2 control-label']) !!}
                    <div class="col-sm-7">
                    {!! Form::password('password_confirmation',['class' => 'form-control' , 'autocomplete' => 'off']) !!}
                        </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-7 col-lg-offset-2">
                {!! Form::submit('Update Your Profile' , ['class' => 'btn btn-lg pull-right btn-primary']) !!}
                        <div>
            </div>

    {!! Form::close() !!}

@stop
