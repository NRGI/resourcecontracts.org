@extends('layout.app')
@section('script')
    <script type="text/template" id="company-template">
        <div class="item">
        <div class="form-group">
        {!! Form::label("company_name", trans("contract.company_name"), ["class"=>"col-sm-2 control-label"])!!}
        <div class="col-sm-7">
        {!! Form::text("company[0][name]",null,["class"=>"form-control"])!!}
        </div>
        </div>

        <div class="form-group">
        {!! Form::label("jurisdiction_of_incorporation", trans("contract.jurisdiction_of_incorporation"),
                ["class"=>"col-sm-2 control-label"])!!}
        <div class="col-sm-7">
        {!! Form::select("company[0][jurisdiction_of_incorporation]", ["" => "select"] + $country ,
                isset($contract->metadata->country->code)?$contract->metadata->country->code:null,
        ["class"=>"form-control template"])!!}
        </div>
        </div>

        <div class="form-group">
        {!! Form::label("registration_agency", trans("contract.registry_agency"), ["class"=>"col-sm-2
        control-label"])!!}
        <div class="col-sm-7">
        {!! Form::text("company[0][registration_agency]",null,["class"=>"form-control"])!!}
        </div>
        </div>

        <div class="form-group">
        {!! Form::label("incorporation_date", trans("contract.incorporation_date"), ["class"=>"col-sm-2
        control-label"])!!}
        <div class="col-sm-7">
        {!! Form::text("company[0][company_founding_date]",null,["class"=>"datepicker form-control",
                "placeholder"
                        => "YYYY-MM-DD"])!!}
        </div>
        </div>

        <div class="form-group">
        {!! Form::label("company_address", trans("contract.company_address"), ["class"=>"col-sm-2
        control-label"])!!}
        <div class="col-sm-7">
        {!! Form::text("company[0][company_address]",null,["class"=>"form-control"])!!}
        </div>
        </div>

        <div class="form-group">
        {!! Form::label("company_number", trans("contract.company_number"), ["class"=>"col-sm-2
        control-label"])!!}
        <div class="col-sm-7">
        {!! Form::text("company[0][company_number]",null,["class"=>"form-control"])!!}
        </div>
        </div>

        <div class="form-group">
        {!! Form::label("parent_company", trans("contract.corporate_grouping"), ["class"=>"col-sm-2
        control-label"])!!}
        <div class="col-sm-7">
        {!! Form::text("company[0][parent_company]",null,["class"=>"form-control"])!!}
        </div>
        </div>


        <div class="form-group">
        <a href="http://opencorporates.com" target="_blank"><i class="glyphicon glyphicon-link"></i> {!!
                Form::label("open_corporate_id",trans("contract.open_corporate_id"), ["class"=>"col-sm-2
        control-label"])!!}</a>

        <div class="col-sm-7">
        {!! Form::text("company[0][open_corporate_id]",null,["class"=>"digit form-control"])!!}
        </div>
        </div>
        </div>
    </script>
@end
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('contract.add')</div>
        <div class="panel-body">
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
            {!! Form::open(['route' => 'contract.store', 'class'=>'form-horizontal contract-form', 'files' => true]) !!}
            @include('contract.form', ['action'=>'add'])
            {!! Form::close() !!}
        </div>
    </div>
@stop
