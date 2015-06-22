<?php
use App\Nrgi\Entities\Contract\Annotation;
?>
<li><strong>@lang('Annotation'):</strong>
    @if($annotationStatus == Annotation::PUBLISHED)
        <span class="published">@lang('Published')</span>
    @elseif($annotationStatus == Annotation::COMPLETED)
       <span class="complete">@lang('Completed')</span>
    @if($current_user->hasRole('superadmin') || $current_user->can('publish-annotation') )
            <div class="pull-right">
            {!!Form::open(['route'=>['contract.annotations.status', $contract->id], 'style'=>"display:inline",
            'method'=>'post'])!!}
            {!!Form::hidden('state', 'published')!!}
            {!!Form::hidden('type', 'text')!!}
            {!!Form::button(trans('Publish'), ['type'=>'submit','class'=>'btn btn-success  confirm ',
            'data-confirm'=>trans('Are you sure you want to publish annotation for this contract?')])!!}
            {!!Form::close()!!}
            <button data-toggle="modal" data-target=".contract-annotation-reject-modal" class="btn btn-danger ">
                @lang('Reject')
            </button>
           </div>

            <div class="modal fade contract-annotation-reject-modal" tabindex="-1" role="dialog"
                 aria-labelledby="contract-annotation-reject-modal"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        {!! Form::open(['route' => ['contract.annotations.comment', $contract->id],
                        'class'=>'suggestion-form']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">@lang('Suggest changes for Annotation')</h4>
                        </div>
                        <div class="modal-body">
                                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                                        'style'=>'width:100%'])!!}
                                        {!!Form::hidden('type', 'text')!!}
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
        <span class="rejected"> @lang('Rejected')</span>
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
                        <h4 class="modal-title" id="myModalLabel">@lang('Comment')</h4>
                    </div>
                    <div class="modal-body">
                        {!!nl2br($contract->annotation_comment->message)!!}
                        <hr style="margin-top: 50px;"/>
                        <p style="font-size: 13px;"><strong>Commented
                                by</strong> {{$contract->annotation_comment->user->name}}
                            on {{$contract->annotation_comment->user->created_at->format('D F d, h:i A')}}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal">@lang('contract.close')</button>
                    </div>
                </div>
            </div>
        </div>
    @else
       <span class="draft"> @lang('Draft')</span>
        @if($current_user->hasRole('superadmin') || $current_user->can('complete-annotation') )
            <div class="pull-right">
            {!!Form::open(['route'=>['contract.annotations.status', $contract->id], 'style'=>"display:inline",
            'method'=>'post'])!!}
            {!!Form::hidden('state', 'completed')!!}
            {!!Form::hidden('type', 'text')!!}
            {!!Form::button(trans('Make Complete'), ['type'=>'submit','class'=>'btn btn-primary  confirm',
            'data-confirm'=>trans('Are you sure you want to marked complete annotations for this contract ?')])!!}
            {!!Form::close()!!}
            </div>
        @endif
    @endif
</li>