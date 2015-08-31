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
            <th class="col-md-6">Metadata</th><th class="col-md-2">Present</th><th class="col-md-2">Missing</th>
            </thead>
            @foreach($data['metadata'] as $key=>$value)
                <tr>
                    <td>{{ucwords(join(' ', explode('_', $key)))}}</td><td> <a href="{{route('contract.index',["type"=>"metadata","word"=>$key,"issue"=>"present"])}}">{{$value}}</a></td><td><a href="{{route('contract.index',["type"=>"metadata","word"=>$key,"issue"=>"missing"])}}">{{$data['total']-$value}}</td></a>
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
                    <th class="col-md-6">Annotations Category</th><th class="col-md-2">Present</th><th class="col-md-2">Missing</th>
                    </thead>
                    <tbody>
                    @foreach($data['annotations'] as $key=>$value)
                        <tr>
                            <td>{{$key}}</td><td><a href="{{route('contract.index',["type"=>"annotations","word"=>$key,"issue"=>"present"])}}">{{$value}}</a></td><td><a href="{{route('contract.index',["type"=>"annotations","word"=>$key,"issue"=>"missing"])}}">{{$data['total']-$value}}</a></td>
                        </tr>
                     @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
