@extends('layout.app')
<?php
$status = \Input::get('status',1)
?>
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('Contracts Sent for Mturk')
        <a class="btn btn-primary pull-right" href="{{route('mturk.activity')}}">@lang('mturk.activity')</a>
    </div>
        <div class="panel-body">
            <div class="btn-group" role="group">
                <a class="btn @if($status == 1) btn-primary @else btn-default @endif" href="{{route('mturk.index')}}?status=1">Pending Contracts</a>
                <a class="btn @if($status == 2) btn-primary @else btn-default @endif" href="{{route('mturk.index')}}?status=2">Completed Contracts</a>
            </div>

            <a class="btn btn-primary pull-right" href="{{route('mturk.allTasks')}}">All Tasks</a>

            @if($status == 2)
                @include('mturk.status.completed')
            @else
                @include('mturk.status.pending')
            @endif

        </div>
    </div>
@stop
