@extends('layout.app')
<?php
$status = \Request::only('status', 1);
$category = \Request::only('category', 'all');
?>
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('mturk.contracts_sent_for_mturk')
            <a class="btn btn-primary pull-right" href="{{route('mturk.activity')}}">@lang('mturk.activity')</a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-11">
                        {!! Form::open(['route' => 'mturk.index', 'method' => 'get','class' => 'form-inline pull-left']) !!}

                        {!! Form::label('status', trans('mturk.status'), ['class' => 'control-label']) !!}
                        {!! Form::select('status', [1=>trans('mturk.pending'),2=>trans('mturk.completed')] , $status , ['class' => 'form-control']) !!}

                        {!! Form::label('category',trans('mturk.category'), ['class' => 'control-label']) !!}
                        {!! Form::select('category', ['all'=>trans('mturk.all'),'rc'=>'RC','olc'=>'OLC'] , $category , ['class' => 'form-control']) !!}

                        {!! Form::submit(trans('mturk.search'), ['class' => 'form-control btn btn-primary']) !!}
                        {!! Form::close() !!}

                        {!! Form::open(['route' => 'mturk.allTasks', 'method' => 'get']) !!}


                        <div class="col-lg-5">
                            <div class="input-group">
                                {!! Form::text('hitid', null , ['class' => 'form-control' , 'placeholder' => trans('mturk.search_hitid')]) !!}
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"> </span></button>
                                     </span></div>
                        </div>

                        {!! Form::close() !!}

                    </div>
                    <div class="col-md-1">
                        <a class="btn btn-primary pull-right" href="{{route('mturk.allTasks')}}">@lang('mturk.all_task')</a>
                    </div>

                </div>

                @if($status == 2)
                    @include('mturk.status.completed')
                @else
                    @include('mturk.status.pending')
                @endif

            </div>
        </div>
@stop
