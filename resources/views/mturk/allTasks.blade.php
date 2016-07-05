@extends('layout.app')
<?php
$status = \Input::get('status', null);
$approved = \Input::get('approved', null);
?>
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            @if($show_options)
               @lang('mturk.all_task')
            @else
                @lang('mturk.task')
            @endif
            <a class="btn btn-default pull-right" href="{{route('mturk.index')}}">@lang('mturk.back')</a>
        </div>
        <div class="panel-body">
            @if($show_options)
                <div class="btn-group" role="group">
                    <a class="btn @if($status == null AND $approved == null) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}">@lang('mturk.all_hit')</a>
                    <a class="btn @if($status == 1 AND $approved == 0) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=1&approved=0">@lang('mturk.requiring_action')</a>
                    <a class="btn @if($status == 1 AND $approved == 1) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=1&approved=1">@lang('mturk.approved')</a>
                    <a class="btn @if($status == 1 AND $approved == 2) btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=1&approved=2">@lang('mturk.rejected')</a>
                    <a class="btn @if($status == '0' AND $approved == '0') btn-primary @else btn-default @endif" href="{{route('mturk.allTasks')}}?status=0&approved=0">@lang('mturk.pending')</a>
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
            @endif


            <table class="table table-striped table-responsive">
                <thead>
                <tr>
                    <th width="25%">{{ trans('mturk.contract_name') }}</th>
                    <th>@lang('mturk.hit_id')</th>
                    <th>@lang('mturk.page_no')</th>
                    <th>@lang('mturk.status')</th>
                    <th>@lang('mturk.approved') ?</th>
                    <th width="15%">@lang('user.created_on')</th>
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
                            @if($task->status == \App\Nrgi\Mturk\Entities\Task::PENDING)
                                {{$task->hit_id}}
                            @else
                                <a href="{{route('mturk.task.detail',['contract_id'=>$task->contract_id, 'task_id'=>$task->id])}}">
                                    {{$task->hit_id}}
                                </a>
                            @endif
                            <a href="{{ hit_url($task->hit_id) }}" target="_blank" title="@lang('mturk.view_on_amazon')" data-toggle="tooltip"> <span class="glyphicon glyphicon-eye-open"></span></a>
                        </td>
                        <td>{{$task->page_no}}</td>
                        <td>{{_l('mturk.'. $task->status()) }} </td>
                        <td>{{_l('mturk.'.$task->approved())}} </td>
                        <td>{{$task->created_at->format('Y-m-d h:i:s A')}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">{{ trans('mturk.task_not_found') }}</td>
                    </tr>
                @endforelse

                </tbody>
            </table>

            @if ($tasks->lastPage()>1)
                <div class="text-center">
                    <div class="pagination-text">
                        @lang('contract.showing') {{($tasks->currentPage()==1)?"1":($tasks->currentPage()-1)*$tasks->perPage()}}
                        @lang('contract.to') {{($tasks->currentPage()== $tasks->lastPage())?$tasks->total():($tasks->currentPage())*$tasks->perPage()}} @lang('contract.of') {{$tasks->total()}} @lang('mturk.index_task')
                    </div>
                    {!! $tasks->appends($app->request->all())->render() !!}
                </div>
            @endif
        </div>
    </div>
@stop



