@extends('layout.app')

@section('script')
    <script>
        $(function () {
            $('.assignment-reject-modal').on('submit', function (e) {
                if ($('#message').val() == '') {
                    alert('Reason is required');
                    e.preventDefault();
                }
            });
        })
    </script>
@stop

<?php
$get_status = \Input::get('status',null);
$approved = \Input::get('approved',null);
$requiring_action = $status['total_completed']-$status['total_approved']-$status['total_rejected'];
?>

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$contract->title}}
            <a class="btn btn-default pull-right" href="{{route('mturk.index')}}">@lang('mturk.back')</a>
        </div>

        <div class="panel-body">
            <div class="row">
            <div class="col-md-6">
                <ul>
                    <li>{{ trans('mturk.total_pages') }}: {{$total_pages or '0'}}</li>
                    <li>{{ trans('mturk.total_hit') }}: {{$total_hit or '0'}}</li>
                    <li>{{ trans('mturk.completed') }}: {{$status['total_completed'] or '0'}}</li>
                    <li>{{ trans('mturk.approved') }}: {{$status['total_approved'] or '0'}}</li>
                    <li>{{ trans('mturk.rejected') }}: {{$status['total_rejected'] or '0'}}</li>
                    <li>{{ trans('mturk.requiring_action') }}: {{$requiring_action}}</li>
                </ul>
                @if($requiring_action > 1)
                    {!! Form::open(['url' =>route('mturk.task.approveAll',['contract_id'=>$contract->id]), 'method' => 'post']) !!}
                    {!! Form::button(trans('mturk.approve_all'), ['type' =>'submit', 'class' => 'btn btn-success confirm', 'data-confirm'=>'Are you sure you want to approve all assignments?'])!!}
                    {!! Form::close() !!}
                @endif

            </div>

                <div class="btn-group col-md-6" style="margin-top: 50px;" role="group">
                    <a class="btn @if($get_status == null AND $approved == null) btn-primary @else btn-default @endif" href="{{route('mturk.tasks', $contract->id)}}">{{ trans('mturk.all_hit') }}</a>
                    <a class="btn @if($get_status == 1 AND $approved == 0) btn-primary @else btn-default @endif" href="{{route('mturk.tasks', $contract->id)}}?status=1&approved=0">{{ trans('mturk.requiring_action') }}</a>
                    <a class="btn @if($get_status == 1 AND $approved == 1) btn-primary @else btn-default @endif" href="{{route('mturk.tasks', $contract->id)}}?status=1&approved=1">{{ trans('mturk.approved') }}</a>
                    <a class="btn @if($get_status == 1 AND $approved == 2) btn-primary @else btn-default @endif" href="{{route('mturk.tasks', $contract->id)}}?status=1&approved=2">{{ trans('mturk.rejected') }}</a>
                    <a class="btn @if($get_status == '0' AND $approved == '0') btn-primary @else btn-default @endif" href="{{route('mturk.tasks', $contract->id)}}?status=0&approved=0">{{ trans('mturk.pending') }}</a>
                </div>

            </div>


            <table class="table table-striped table-responsive">
                <thead>
                <tr>
                    <th width="30%">{{ trans('mturk.hit_id') }}</th>
                    <th width="5%"></th>
                    <th style="text-align: center;">{{ trans('mturk.page_no') }}</th>
                    <th>{{ trans('mturk.status') }}</th>
                    <th>{{ trans('mturk.approved') }}?</th>
                    <th width="15%">{{ trans('user.created_on') }}</th>
                    <th>{{ trans('user.action') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($contract->tasks as $task)
                    <tr>
                        <td>
                            @if($task->status != 0)
                                <a href="{{route('mturk.task.detail',['contract_id'=>$contract->id, 'task_id'=>$task->id])}}">{{$task->hit_id}}</a>
                            @else
                                {{$task->hit_id}}
                            @endif
                        </td>
                        <td>
                                <a href="{{ hit_url($task->hit_id) }}" target="_blank" title="@lang('mturk.view_on_amazon')" data-toggle="tooltip"> <span class="glyphicon glyphicon-eye-open"></span></a>
                        </td>
                        <td style="text-align:center;">{{$task->page_no}}</td>
                        <td>{{$task->status()}}</td>
                        <td>{{$task->approved()}}</td>
                        <td>{{$task->created_at->format('Y-m-d h:i:s A')}}</td>
                        <td>
                            <div class="mturk-btn-group" role="group">
                                @if($task->status != 0)
                                    <a href="{{route('mturk.task.detail',['contract_id'=>$contract->id, 'task_id'=>$task->id])}}"
                                       class="btn btn-default">@lang('mturk.review')</a>
                                    @if(empty($task->approved))
                                        {!! Form::open(['url' =>route('mturk.task.approve',['contract_id'=>$contract->id, 'task_id'=>$task->id]), 'method' => 'post']) !!}
                                        {!! Form::button(trans('mturk.approve'), ['type' =>'submit', 'class' => 'btn btn-success confirm', 'data-confirm'=>trans('mturk.mturk_approve')])!!}
                                        {!! Form::close() !!}
                                        {!! Form::button(trans('mturk.reject'), ['type' =>'submit', 'class' => 'btn btn-danger', 'data-toggle'=>'modal', 'data-target'=>'.reject-modal-'.$task->id])!!}

                                        <div class="modal fade reject-modal-{{$task->id}}" tabindex="-1" role="dialog"
                                             aria-labelledby="myModalLabel-{{$task->id}}"
                                             aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    {!! Form::open(['url' =>route('mturk.task.reject',['contract_id'=>$contract->id, 'task_id'=>$task->id]), 'method' => 'post']) !!}
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="myModalLabel">@lang('mturk.mturk_rejection')</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                                                        'style'=>'width:100%'])!!}
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default"
                                                                data-dismiss="modal">@lang('global.form.cancel')</button>
                                                        {!! Form::button(trans('mturk.reject'), ['type' =>'submit', 'class' => 'btn btn-danger'])!!}
                                                    </div>
                                                    {!! Form::close() !!}
                                                </div>
                                            </div>
                                        </div>

                                    @endif

                            @endif
                            @if($task->approved == \App\Nrgi\Mturk\Entities\Task::REJECTED || $task->status == \App\Nrgi\Mturk\Entities\Task::PENDING)
                                {!! Form::open(['url' =>route('mturk.task.reset',['contract_id'=>$contract->id, 'task_id'=>$task->id]), 'method' => 'post']) !!}
                                {!! Form::button(trans('mturk.reset'), ['type' =>'submit', 'class' => 'btn btn-primary confirm', 'data-confirm'=>trans('mturk.reset_hitid')])!!}
                                {!! Form::close() !!}
                            @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">{{ trans('mturk.task_not_found') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
