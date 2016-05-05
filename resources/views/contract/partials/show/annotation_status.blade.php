<?php
use App\Nrgi\Entities\Contract\Annotation\Annotation;
?>
@if($annotationStatus == Annotation::PUBLISHED)
    <span class="published">@lang('global.published')</span>
@elseif($annotationStatus == Annotation::COMPLETED)
    <span class="completed">@lang('global.completed')</span>
    @if($current_user->can('publish-annotation') )
        <div class="pull-right">
            <button data-toggle="modal" data-target=".annotation-publish-modal" class="btn btn-success">
                @lang('global.publish')
            </button>
            <button data-toggle="modal" data-target=".annotation-reject-modal" class="btn btn-danger">
                @lang('global.reject')
            </button>
        </div>

        <div class="modal fade annotation-reject-modal" tabindex="-1" role="dialog"
             aria-labelledby="annotation-reject-modal"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.annotations.status', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('annotation.suggest_changes')</h4>
                    </div>
                    <div class="modal-body">
                            {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                             'style'=>'width:100%'])!!}
                             {!!Form::hidden('type', 'annotation')!!}
                             {!!Form::hidden('status', Annotation::REJECTED, ['id'=>"status"])!!}
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
        <div class="modal fade annotation-publish-modal" tabindex="-1" role="dialog"
             aria-labelledby="annotation-publish-modal"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.annotations.status', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('annotation.suggest_changes')</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                        'style'=>'width:100%'])!!}
                        {!!Form::hidden('type', 'annotation')!!}
                        {!!Form::hidden('status', Annotation::PUBLISHED, ['id'=>"status"])!!}
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
@elseif($annotationStatus == Annotation::REJECTED)
    <span class="rejected"> @lang('mturk.rejected')</span>
@else
    <span class="draft"> @lang('global.draft')</span>
    @if($current_user->can('complete-annotation') )
        <div class="pull-right">
            <button data-toggle="modal" data-target=".annotation-complete-modal" class="btn btn-primary">
                @lang('global.complete')
            </button>
        </div>
        <div class="modal fade annotation-complete-modal" tabindex="-1" role="dialog"
             aria-labelledby="annotation-complete-modal"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.annotations.status', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('global.remarks')</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                        'style'=>'width:100%'])!!}
                        {!!Form::hidden('type', 'annotation')!!}
                        {!!Form::hidden('status', Annotation::COMPLETED, ['id'=>"status"])!!}
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

@if($contract->annotation_comment)
<a href="#" data-toggle="modal" data-target=".annotation-reject-msg-modal"><i
            class="glyphicon glyphicon-pushpin"></i></a>

<div class="modal fade annotation-reject-msg-modal" id="annotation-reject-msg-modal" tabindex="-1" role="dialog"
     aria-labelledby="annotation-reject-msg-modal"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">@lang('annotation.all_comments')</h4>
            </div>
            <div class="modal-body">
                @forelse($contract->annotation_comment as $annotation_comment)
                    <div class="comment-section active" id="{{$annotation_comment->type}}">
                        <div class="comment">
                            {{$annotation_comment->message}}
                            <div class="label label-default label-comment">{{ucfirst($annotation_comment->type)}}</div>
                        </div>
                        <div class="comment-info">
                            <span class="{{$annotation_comment->action}}">{{ucfirst($annotation_comment->action)}}</span>
                            @lang('global.by') <strong>{{$annotation_comment->user->name}}</strong>
                            @lang('global.on') {{$annotation_comment->created_at->format('D F d, Y h:i a')}}
                        </div>
                    </div>
                @empty
                    <p>No comment</p>
                @endforelse
            </div>
            <div class="modal-footer">
                <a href="{{route('contract.comment.list',$contract->id)}}"
                   class="btn btn-default pull-left">@lang('global.view_all')</a>
                <button type="button" class="btn btn-default"
                        data-dismiss="modal">@lang('contract.close')</button>
            </div>
        </div>
    </div>
</div>
@endif
