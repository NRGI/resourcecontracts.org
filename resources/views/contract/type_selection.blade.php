@extends('layout.app')

@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <style>
        .add-supporting-contract{
            display: none;
        }
        .parent-contract-field {
            width: 400px;
            padding-bottom: 20px;
        }
    </style>
@stop
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('contract.select_contract_type_title')</div>
        <div class="panel-body">
                <div class="col-sm-12" style="margin-bottom: 25px;">
                    <a href="{{route('contract.create')}}" class="btn btn-primary btn-import">@lang('contract.main_contract')</a>
                    <p class="help-block">@lang('contract.select_main_contract_help_text')</p>
                </div>
                <div class="col-sm-12">
                        <a href="javascript:void(0)" id="supporting-contract" class="btn btn-primary btn-import">@lang('contract.supporting_contract')</a>
                        <p class="help-block">@lang('contract.select_supporting_contract_help_text')</p>
                </div>

            <div class="col-sm-12 add-supporting-contract" id="add_supporting_contract">
            {!! Form::open(['route' => 'contract.create', 'method' => 'get', 'class'=>'supporting-contract-form']) !!}
            <p class="help-block">@lang('contract.select_parent_contract_help_text')</p>
            <div class="parent-contract-field">
                {!! Form::select('parent',[''=>'Select','0'=>'No Parent']+$parentContracts,null, ["class"=>"form-control parent-contract required"])!!}
            </div>
            {!! Form::submit(trans('contract.next'), ['class' => 'btn btn-primary']) !!} or <a class="btn btn-danger supporting-contract-form-hide" href="javascript:void(0)">@lang('contract.cancel')</a>
            {!! Form::close() !!}
            </div>

        </div>
    </div>
@stop

@section('script')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/jquery.validate.min.js')}}"></script>
    <script>
        $(function () {
            $('select').select2({placeholder: "Select", allowClear: true, theme: "classic"});
            $('.supporting-contract-form').validate();
            $(document).on('click', '#supporting-contract', function (e) {
                $('.add-supporting-contract').show();
            });
            $(document).on('click', '.supporting-contract-form-hide', function (e) {
                $('.add-supporting-contract').hide();
            });
        });
    </script>
@stop