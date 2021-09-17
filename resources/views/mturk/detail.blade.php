@extends('layout.app')

@section('content')
	<div class="panel panel-default">
		<div class="panel-heading">{{$contract->title}}
			<a class="btn btn-default pull-right"
			   href="{{route('mturk.allTasks', $contract->id)}}">@lang('mturk.back')</a>
		</div>

		<div class="panel-body">
			<ul>
				<li>@lang('mturk.page_no'): {{$task->page_no}}</li>
				<li>@lang('mturk.hit'): {{$task->hit_id}}</li>
				<li>@lang('mturk.status'): {{_l('mturk.'.$task->status())}}</li>
				<li>@lang('mturk.approved'): {{_l('mturk.'.$task->approved())}} </li>
				<li>@lang('mturk.assignment_id'): {{$task->assignments->assignment->assignment_id}}</li>
				<li>@lang('mturk.worker_id'): {{$task->assignments->assignment->worker_id}}</li>
                <?php
                $submit_time = $task->assignments->assignment->submit_time;

                if (is_int($submit_time)) {
                    $submit_time = \Carbon\Carbon::createFromFormat(
                        'Y-m-d\TH:i:s\Z',
                        date('Y-m-d\TH:i:s\Z', $submit_time)
                    );
                }
                ?>
				<li>@lang('mturk.submit_time'): {{ $submit_time }}</li>
			</ul>

			<div class="row">
				<div class="col-md-6">
					<div class="textarea" style="border: 1px solid #ccc; overflow: scroll; padding: 15px; height:580px">
						{!! nl2br($feedback) !!}
					</div>
				</div>
				<div class="col-md-6">
					<a href="{{$task->pdf_url}}" id="pdf_url"></a>
					@section('script')
						<script src="{{asset('js/jquery.gdocsviewer.min.js')}}"></script>
						<script type="text/javascript">
                            $(document).ready(function () {
                                $('#pdf_url').gdocsViewer({width: 450, height: 580});
                            });
						</script>
					@stop
				</div>
			</div>

			@if(empty($task->approved))
				<div class="mturk-btn-group">
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
									<h4 class="modal-title" id="myModalLabel">@lang('mturk.reject_task')</h4>
								</div>
								<div class="modal-body">
									{!! Form::textarea('message', null, ['id'=>"message","placeholder"=>trans('mturk.mturk_rejection'), 'rows'=>12,
									'placeholder'=>trans('mturk.rejection_reason'))
									'style'=>'width:100%'])!!}
									{!! Form::textarea('description', isset($task->hit_description) ? $task->hit_description : null, ['id'=>"message", 'rows'=>6,
									'placeholder'=>trans('mturk.hit_description'),
									'style'=>'width:100%; margin-top:10px;margin-bottom:10px'])!!}
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
				</div>
			@endif
		</div>
	</div>
@stop
