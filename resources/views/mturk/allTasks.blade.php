@extends('layout.app')
<?php
$status = \Input::get('status',null);
$approved = \Input::get('approved',null);
?>
@section('content')
<div class="panel panel-default">
    <div class="panel-heading">All Tasks
        <a class="btn btn-default pull-right" href="{{route('mturk.index')}}">@lang('Back')</a>
    </div>
    <div class="panel-body">
        <div class="btn-group" role="group">
            <a class="btn @if($status == null AND $approved == null) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}">All HIT</a>
            <a class="btn @if($status == 1 AND $approved == 0) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=1&approved=0">Requiring Action</a>
            <a class="btn @if($status == 1 AND $approved == 1) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=1&approved=1">Approved</a>
            <a class="btn @if($status == 1 AND $approved == 2) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=1&approved=2">Rejected</a>
            <a class="btn @if($status == '0' AND $approved == '0') btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=0&approved=0">Pending</a>
        </div>

        <table class="table table-striped table-responsive">
            <thead>
            <tr>
                <th width="25%">Contract Name</th>
                <th>HIT ID</th>
                <th>Page no.</th>
                <th>Status</th>
                <th>Approved?</th>
                <th width="15%">Created on</th>
            </tr>
            </thead>
            <tbody>

            @forelse($tasks as $task)
                <?php $contract = json_decode($task->metadata);?>
                <tr>
                    <td><a href="{{route('contract.show',$task->contract_id)}}">
                            {{$contract->contract_name}}
                        </a>
                            - {{$contract->country->name}}
                       </td>
                    <td>
                        <a href="{{route('mturk.task.detail',['contract_id'=>$task->contract_id, 'task_id'=>$task->id])}}">
                        {{$task->hit_id}}
                        </a>
                    </td>
                    <td>{{$task->page_no}}</td>
                    <td>{{$task->status()}}</td>
                    <td>{{$task->approved()}}</td>
                    <td>{{$task->created_at->format('Y-m-d h:i:s A')}}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tasks not found.</td>
                </tr>
            @endforelse

            </tbody>
        </table>

        @if ($tasks->lastPage()>1)
            <div class="text-center">
                <div class="pagination-text">
                    Showing {{($tasks->currentPage()==1)?"1":($tasks->currentPage()-1)*$tasks->perPage()}} to {{($tasks->currentPage()== $tasks->lastPage())?$tasks->total():($tasks->currentPage())*$tasks->perPage()}} of {{$tasks->total()}} tasks
                </div>
                {!! $tasks->appends($app->request->all())->render() !!}
            </div>
        @endif
    </div>
</div>
@stop



