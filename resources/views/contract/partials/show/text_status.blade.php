<?php
use App\Nrgi\Entities\Contract\Contract; ?>
<strong>@lang('PDF Text'):</strong>
@if($contract->text_status == Contract::STATUS_PUBLISHED)
    <span class="published">   @lang('Published')</span>
@elseif($contract->text_status == Contract::STATUS_COMPLETED)
    <span class="completed"> @lang('Completed')</span>
    @if($current_user->can('publish-text') )
        <div class="pull-right">
            <button data-toggle="modal" data-target=".text-publish-modal" class="btn btn-success">
                @lang('Publish')
            </button>
            <button data-toggle="modal" data-target=".text-reject-modal" class="btn btn-danger">
                @lang('Reject')
            </button>
        </div>
        <div class="modal fade text-publish-modal" tabindex="-1" role="dialog"
             aria-labelledby="text-publish-modal"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                                aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>

                        <h4 class="modal-title" id="myModalLabel">@lang('Remarks')</h4>

                    </div>
                    <div class="modal-body">
                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                        'style'=>'width:100%'])!!}
                        {!!Form::hidden('type', 'text')!!}
                        {!!Form::hidden('status', Contract::STATUS_PUBLISHED, ['id'=>"status"])!!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal">@lang('global.form.cancel')</button>
                        <button type="submit"
                                class="btn btn-primary">@lang('global.form.ok')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
        <div class="modal fade text-reject-modal" tabindex="-1" role="dialog"
             aria-labelledby="text-reject-modal"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                                aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>

                        <h4 class="modal-title" id="myModalLabel">@lang('Suggest changes for Text')</h4>

                    </div>
                    <div class="modal-body">
                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                        'style'=>'width:100%'])!!}
                        {!!Form::hidden('type', 'text')!!}
                        {!!Form::hidden('status', Contract::STATUS_REJECTED, ['id'=>"status"])!!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal">@lang('global.form.cancel')</button>
                        <button type="submit"
                                class="btn btn-primary">@lang('global.form.ok')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    @endif
@elseif($contract->text_status == Contract::STATUS_REJECTED)
    <span class="rejected">@lang('Rejected')</span>
@else
    <span class="draft"> @lang('Draft')</span>
    @if($current_user->can('complete-text') )
        <div class="pull-right">
            <button data-toggle="modal" data-target=".text-complete-modal" class="btn btn-primary">
                @lang('Complete')
            </button>
        </div>
        <div class="modal fade text-complete-modal" tabindex="-1" role="dialog"
             aria-labelledby="text-complete-modal"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"
                                aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>

                        <h4 class="modal-title" id="myModalLabel">@lang('Remarks')</h4>

                    </div>
                    <div class="modal-body">
                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                        'style'=>'width:100%'])!!}
                        {!!Form::hidden('type', 'text')!!}
                        {!!Form::hidden('status', Contract::STATUS_COMPLETED, ['id'=>"status"])!!}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal">@lang('global.form.cancel')</button>
                        <button type="submit"
                                class="btn btn-primary">@lang('global.form.ok')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    @endif
@endif

@if($contract->text_comment)
    <a href="#" data-toggle="modal" data-target=".text-reject-msg-modal"><i
                class="glyphicon glyphicon-pushpin"></i></a>
    <div class="modal fade text-reject-msg-modal" id="text-reject-msg-modal" tabindex="-1" role="dialog"
         aria-labelledby="text-reject-msg-modal"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">@lang('All Comments Pdf Text')</h4>
                </div>
                <div class="modal-body">
                    @forelse($contract->text_comment as $text_comment)
                        <div class="comment-section active" id="{{$text_comment->type}}">
                            <div class="comment">
                                {{$text_comment->message}}
                                <div class="label label-default label-comment">{{ucfirst($text_comment->type)}}</div>
                            </div>
                            <div class="comment-info">
                                <span class="{{$text_comment->action}}">{{ucfirst($text_comment->action)}}</span>
                                @lang('by') <strong>{{$text_comment->user->name}}</strong>
                                @lang('on') {{$text_comment->created_at->format('D F d, Y h:i a')}}
                            </div>
                        </div>
                    @empty
                       <p> @lang("no comment")</p>
                    @endforelse
                </div>
                <div class="modal-footer">
                    <a href="{{route('contract.comment.list',$contract->id)}}"
                       class="btn btn-default pull-left">@lang('View All')</a>
                    <button type="button" class="btn btn-default"
                            data-dismiss="modal">@lang('contract.close')</button>
                </div>
            </div>
        </div>
    </div>
@endif
<a style="padding-left: 5px" href="{{route('contract.review', ['id'=>$contract->id])}}">@lang('contract.review_text')</a>
