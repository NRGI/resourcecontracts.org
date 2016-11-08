@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{ trans('user.edit_user') }} {{$user->name}}</div>
        <div class="panel-body">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>@lang('user.whoops')</strong> {{ trans('user.input_error') }}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! Form::model($user, ['route' => ['user.update', $user->id], 'method' => 'PATCH',
            'class'=>'form-horizontal']) !!}
            @include('user.form', ['action' =>'update'])
            {!! Form::close() !!}
        </div>
    </div>
@endsection
