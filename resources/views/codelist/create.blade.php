@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{ trans('codelist.add_'.$type) }}</div>
        <div class="panel-body">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>@lang('codelist.whoops')</strong> {{ trans('codelist.input_error') }}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! Form::open(['route' => 'codelist.store', 'method' => 'post', 'class'=>'form-horizontal']) !!}
                @include('codelist.form', ['action' =>'add'])
            {!! Form::close() !!}
        </div>
    </div>
@endsection


