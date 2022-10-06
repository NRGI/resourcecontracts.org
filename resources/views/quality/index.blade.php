@extends('layout.app')
@section('css')
    <style>
        .panel-heading
        {
            background-color: #f0f0f0;
        }
        .quality-form{
            padding-top: 10px;
            padding-left: 10px;
        }

        .select2 {
            width: 18% !important;
            float: left;
            margin-right: 20px !important;
            margin-top: 4px !important;
        }

        @media (min-width: 768px){
            .form-inline .form-control {
                margin-right: 10px;
            }

        }
        .panel-heading .description{
            font-size: 15px;
        }

    </style>
@stop
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3>{{trans('quality.contract_quality_issue')}}</h3>
            <p class="description">{{trans('quality.filter_description')}}</p>
        </div>
        <div class="quality-form">
            {!! Form::open(['route' => 'quality.index', 'method' => 'get', 'class'=>'form-inline']) !!}
            {!! Form::select('year', ['all'=>trans('contract.year')] + $years , Request::input('year') , ['class' =>
            'form-control','style'=>'width:200px']) !!}

            {!! Form::select('country', ['all'=>trans('contract.country')] + $countries , Request::input('country') ,
            ['class' =>'form-control','style'=>'width:200px']) !!}

            {!! Form::select('category', ['all'=>trans('contract.category')] + config('metadata.category'),
            Request::input('category') ,
            ['class' =>'form-control','style'=>'width:200px']) !!}

            {!! Form::select('resource', ['all'=>trans('contract.resource')] + trans_array($resources,
            'codelist/resource') ,
            Request::input
            ('resource') ,
            ['class' =>'form-control','style'=>'width:200px']) !!}


            {!! Form::submit(trans('quality.filter'), ['class' => 'btn btn-primary','style'=>'width:130px']) !!}
            {!! Form::close() !!}
        </div>

        <br>
    <div class="metadata-quality">
        <div class="panel-heading">

            <h3>{{trans('quality.metadata_heading')}}</h3>
            {{trans('quality.metadata_description')}}
        </div>
        <div class="panel-body">


            <table class="table">
            <thead>
            <th class="col-md-6">{{ trans('quality.metadata') }}</th><th class="col-md-2">{{ trans('quality.present') }}</th><th class="col-md-2">{{ trans('quality.missing') }}</th>
            </thead>
            @foreach($data['metadata'] as $key=>$value)
                <tr>
                    <?php

                       $metadataPresent = array_merge(["type"=>"metadata","word"=>$key,"issue"=>"present"],$filters);
                       $metadataMissing = array_merge(["type"=>"metadata","word"=>$key,"issue"=>"missing"],$filters);
                       ?>

                    <td>{{trans('contract.'.$key)}}</td><td> <a href="{{route('contract.index',$metadataPresent)}}">{{$value}}</a></td><td><a href="{{route('contract.index',$metadataMissing)}}">{{$data['total']-$value}}</td></a>
                </tr>
            @endforeach
        </table>
            </div>
    </div>

        <div class="annotations-quality">
            <div class="panel-heading">
                <h3>{{trans('quality.annotations_heading')}}</h3>
                    {{trans('quality.annotations_description')}}
            </div>
            <div class="panel-body">
                <table class="table">
                    <thead>
                    <th class="col-md-6">{{ trans('quality.anotations_category') }}</th><th class="col-md-2">{{ trans('quality.present') }}</th><th class="col-md-2">{{ trans('quality.missing') }}</th>
                    </thead>
                    <tbody>
                    @foreach($data['annotations'] as $key=>$value)
                        <tr>
                            <?php
                                    $annotationPresent = array_merge(["type"=>"annotations","word"=>$key,"issue"=>"present"],$filters);
                                    $annotationMissing = array_merge(["type"=>"annotations","word"=>$key,"issue"=>"missing"],$filters);
                               ?>
                            <td>{{_l("codelist/annotation.annotation_category.{$key}")}}</td><td><a href="{{route('contract.index',$annotationPresent)}}">{{$value}}</a></td><td><a href="{{route('contract.index',$annotationMissing)}}">{{$data['total']-$value}}</a></td>
                        </tr>
                     @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
@section('script')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script type="text/javascript">
        var lang_select = '@lang('global.select')';
        $('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
    </script>
@stop
