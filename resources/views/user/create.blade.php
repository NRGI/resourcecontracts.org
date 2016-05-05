@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{ trans('user.create_user') }}</div>
        <div class="panel-body">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> {{ trans('user.input_error') }}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! Form::open(['route' => 'user.store', 'method' => 'post', 'class'=>'form-horizontal']) !!}
                @include('user.form', ['action' =>'add'])
            {!! Form::close() !!}
        </div>
    </div>
@endsection

