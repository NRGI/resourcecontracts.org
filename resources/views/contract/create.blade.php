@extends('layout.app')
@section('content')
    <?php $is_supporting = Request::has('parent');?>
    <div class="panel panel-default">
        <div class="panel-heading">@if($is_supporting) @lang('contract.add_supporting_document') @else @lang('contract.add')@endif</div>
        <div class="panel-body contract-wrapper" data-id="{{formatIdRorName($contract_id)}}">
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
            {!! Form::model($contract,['route' => 'contract.store','class'=>'form-horizontal contract-form', 'files' => true]) !!}
            @include('contract.form', ['action'=>'add'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
