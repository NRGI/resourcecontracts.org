@extends('layout.app')
@section('css')
    <style>
        .panel-heading
        {
            background-color: #f0f0f0;
        }
    </style>
@stop
@section('content')
    <div class="panel panel-default">
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
                    <td>{{trans('contract.'.$key)}}</td><td> <a href="{{route('contract.index',["type"=>"metadata","word"=>$key,"issue"=>"present"])}}">{{$value}}</a></td><td><a href="{{route('contract.index',["type"=>"metadata","word"=>$key,"issue"=>"missing"])}}">{{$data['total']-$value}}</td></a>
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
                            <td>{{_l("codelist/annotation.annotation_category.{$key}")}}</td><td><a href="{{route('contract.index',["type"=>"annotations","word"=>$key,"issue"=>"present"])}}">{{$value}}</a></td><td><a href="{{route('contract.index',["type"=>"annotations","word"=>$key,"issue"=>"missing"])}}">{{$data['total']-$value}}</a></td>
                        </tr>
                     @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
