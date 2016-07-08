@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading"> Editing <span>{{$contract->metadata->contract_name or $contract->metadata->project_title}}</span></div>
        <div class="panel-body contract-wrapper" data-id="{{formatIdRorName($contract->id)}}">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>@lang('contract.whoops')</strong> @lang('contract.problem')<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            {!! Form::model($contract,['route' => array('contract.update', $contract->id) ,  'class'=>'form-horizontal contract-form', 'method'=>'PATCH',
            'files' => true]) !!}
            @include('contract.form', ['action'=>'edit', 'contact' => $contract])
            {!! Form::close() !!}
        </div>
    </div>
@stop