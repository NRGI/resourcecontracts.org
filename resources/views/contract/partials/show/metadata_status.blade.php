<?php
use App\Nrgi\Entities\Contract\Contract; ?>
<strong>@lang('global.metadata'):</strong>
@if($contract->metadata_status == Contract::STATUS_PUBLISHED)
    <span class="published">@lang('global.published')</span>
@elseif($contract->metadata_status == Contract::STATUS_COMPLETED)
    <span class="completed">@lang('global.completed')</span>
    @if(null==($current_user->isCountryResearch()) || ($current_user->isCountryResearch()==false))
       <div class="pull-right">
            <button data-toggle="modal" data-target=".metadata-publish-modal" class="btn btn-success">@lang("global.publish")
            </button>
            <button data-toggle="modal" data-target=".metadata-reject-modal" class="btn btn-danger">@lang("global.reject")
            </button>
        </div>
    @endif

        <div class="modal fade metadata-reject-modal" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('global.suggest')</h4>
                    </div>

                    <div class="modal-body">
                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                        'style'=>'width:100%'])!!}
                        {!!Form::hidden('type', 'metadata')!!}
                        {!!Form::hidden('status', Contract::STATUS_REJECTED , ['id'=>"status"])!!}
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
        <div class="modal fade metadata-publish-modal" tabindex="-1" role="dialog"
             aria-labelledby="myModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                    'class'=>'suggestion-form']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('global.remarks')</h4>
                    </div>
                    <div class="modal-body">
                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                        'style'=>'width:100%'])!!}
                        {!!Form::hidden('type', 'metadata')!!}
                        {!!Form::hidden('status', Contract::STATUS_PUBLISHED ,['id'=>"status"])!!}
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
@elseif($contract->metadata_status == Contract::STATUS_REJECTED)
    <span class="rejected">@lang('mturk.rejected')</span>
@else
    <span class="draft">@lang('global.draft')</span>
        <div class="pull-right">
            <button data-toggle="modal" data-target=".metadata-complete-modal" class="btn btn-primary">@lang('global.complete')
            </button>
            <div class="modal fade metadata-complete-modal" tabindex="-1" role="dialog"
                 aria-labelledby="myModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                        'class'=>'suggestion-form']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">@lang('global.comment')</h4>
                        </div>
                        <div class="modal-body">
                            {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                            'style'=>'width:100%'])!!}
                            {!!Form::hidden('type', 'metadata')!!}
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
        </div>
@endif

@if($contract->metadata_comment)
    <a href="#" data-toggle="modal" data-target=".metadata-modal"><i
                class="glyphicon glyphicon-pushpin"></i></a>
    <div class="modal fade metadata-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">@lang('global.all_comments')</h4>
                </div>
                <div class="modal-body">
                    @forelse($contract->metadata_comment as $metadata_comment)
                        <div class="comment-section active" id="{{$metadata_comment->type}}">
                            <div class="comment">
                                {{$metadata_comment->message}}
                                <div class="label label-default label-comment">{{ucfirst($metadata_comment->type)}}</div>
                            </div>
                            <div class="comment-info">
                                <span class="{{$metadata_comment->action}}">{{ucfirst($metadata_comment->action)}}</span>
                                @lang('global.by') <strong>{{$metadata_comment->user->name}}</strong>
                                @lang('global.on') {{$metadata_comment->created_at->format('D F d, Y h:i a')}}
                            </div>
                        </div>
                    @empty
                        @lang("global.no_comment")
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

<a style="padding-left: 10px" href="{{route('contract.edit', $contract->id)}}" >@lang('contract.edit_metadata')</a>