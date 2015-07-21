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


@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$contract->title}}
            <a class="btn btn-default pull-right" href="{{route('mturk.index')}}">@lang('Back')</a>
        </div>
        <div class="panel-body">
            <ul>
                <li>Total Pages: {{$total_pages or '0'}}</li>
                <li>Total HIT: {{$total_hit or '0'}}</li>
                <li>Completed: {{$status['total_completed'] or '0'}}</li>
                <li>Approved: {{$status['total_approved'] or '0'}}</li>
                <li>Rejected: {{$status['total_rejected'] or '0'}}</li>
            </ul>

            <table class="table table-striped table-responsive">
                <thead>
                <tr>
                    <th>HIT ID</th>
                    <th>Page no.</th>
                    <th>Status</th>
                    <th>Approved?</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($contract->tasks as $task)
                    <tr>
                        <td>{{$task->hit_id}}</td>
                        <td>{{$task->page_no}}</td>
                        <td>{{$task->status()}}</td>
                        <td>{{$task->approved()}}</td>
                        <td>
                            <div class="mturk-btn-group" role="group">
                                @if($task->status != 0)
                                    <a href="{{route('mturk.task.detail',['contract_id'=>$contract->id, 'task_id'=>$task->id])}}"
                                       class="btn btn-default">Review</a>
                                    @if(empty($task->approved))
                                        {!! Form::open(['url' =>route('mturk.task.approve',['contract_id'=>$contract->id, 'task_id'=>$task->id]), 'method' => 'post']) !!}
                                        {!! Form::button(trans('Approve'), ['type' =>'submit', 'class' => 'btn btn-success confirm', 'data-confirm'=>'Are you sure you want to approve this
                                        assignment?'])!!}
                                        {!! Form::close() !!}
                                        {!! Form::button(trans('Reject'), ['type' =>'submit', 'class' => 'btn btn-danger', 'data-toggle'=>'modal', 'data-target'=>'.reject-modal-'.$task->id])!!}

                                        <div class="modal fade reject-modal-{{$task->id}}" tabindex="-1" role="dialog"
                                             aria-labelledby="myModalLabel-{{$task->id}}"
                                             aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    {!! Form::open(['url' =>route('mturk.task.reject',['contract_id'=>$contract->id, 'task_id'=>$task->id]), 'method' => 'post']) !!}
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                                    aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="myModalLabel">@lang('Write reason for rejection')</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                                                        'style'=>'width:100%'])!!}
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default"
                                                                data-dismiss="modal">@lang('global.form.cancel')</button>
                                                        {!! Form::button(trans('Reject'), ['type' =>'submit', 'class' => 'btn btn-danger'])!!}
                                                    </div>
                                                    {!! Form::close() !!}
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($task->approved == \App\Nrgi\Mturk\Entities\Task::REJECTED)
                                        {!! Form::open(['url' =>route('mturk.task.reset',['contract_id'=>$contract->id, 'task_id'=>$task->id]), 'method' => 'post']) !!}
                                        {!! Form::button(trans('Reset'), ['type' =>'submit', 'class' => 'btn btn-primary confirm', 'data-confirm'=>'Are you sure you want to reset this
                                        assignment?'])!!}
                                        {!! Form::close() !!}
                                    @endif
                            </div>

                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">Contract not found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop



