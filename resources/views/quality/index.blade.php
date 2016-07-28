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
            {!! Form::open(['route' => 'quality.index', 'method' => 'get', 'class'=>'form-inline']) !!}
            {!! Form::select('year', ['all'=>trans('contract.year')] + $years , Input::get('year') , ['class' =>
            'form-control']) !!}

            {!! Form::select('country', ['all'=>trans('contract.country')] + $countries , Input::get('country') ,
            ['class' =>'form-control']) !!}

            {!! Form::select('category', ['all'=>trans('contract.category')] + config('metadata.category'),
            Input::get('category') ,
            ['class' =>'form-control']) !!}

            {!! Form::select('resource', ['all'=>trans('contract.resource')] + trans_array($resources,
            'codelist/resource') ,
            Input::get
            ('resource') ,
            ['class' =>'form-control']) !!}


            {!! Form::submit(trans('contract.search'), ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}

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
