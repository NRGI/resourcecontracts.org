@extends('layout.app')
<?php
$status = \Input::get('status', 1);
$category = \Input::get('category', 'all');
?>
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('Contracts Sent for Mturk')
            <a class="btn btn-primary pull-right" href="{{route('mturk.activity')}}">@lang('mturk.activity')</a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="col-lg-11">
                        {!! Form::open(['route' => 'mturk.index', 'method' => 'get','class' => 'form-inline pull-left']) !!}

                        {!! Form::label('status', 'Status: ', ['class' => 'control-label']) !!}
                        {!! Form::select('status', [1=>'Pending',2=>'Completed'] , $status , ['class' => 'form-control']) !!}

                        {!! Form::label('category', 'Category: ', ['class' => 'control-label']) !!}
                        {!! Form::select('category', ['all'=>'All','rc'=>'RC','olc'=>'OLC'] , $category , ['class' => 'form-control']) !!}

                        {!! Form::submit('Search', ['class' => 'form-control btn btn-primary']) !!}
                        {!! Form::close() !!}

                        {!! Form::open(['route' => 'mturk.allTasks', 'method' => 'get']) !!}


                        <div class="col-lg-5">
                            <div class="input-group">
                                {!! Form::text('hitid', null , ['class' => 'form-control' , 'placeholder' => 'Search By HIT Id']) !!}
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"> </span></button>
                                     </span></div>
                        </div>

                        {!! Form::close() !!}

                    </div>
                    <div class="col-md-1">
                        <a class="btn btn-primary pull-right" href="{{route('mturk.allTasks')}}">All Tasks</a>
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
