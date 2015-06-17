<?php
use App\Nrgi\Entities\Contract\Contract;
?>

<div class="annotation-wrap" style="margin: 20px 0px">
    <p> @lang('Contract State'):</p>
    <ul>
        <li>
            <strong>@lang('Metadata'):</strong>
            @if($contract->metadata_status == Contract::STATUS_PUBLISHED)
                @lang('Published')
            @elseif($contract->metadata_status == Contract::STATUS_COMPLETED)
                @lang('Completed')
                @if($current_user->hasRole('superadmin') || $current_user->can('publish-metadata') )
                    {!!Form::open(['route'=>['contract.status', $contract->id], 'style'=>"display:inline",
                    'method'=>'post'])!!}
                    {!!Form::hidden('state', 'published')!!}
                    {!!Form::hidden('type', 'metadata')!!}
                    {!!Form::button(trans('Publish'), ['type'=>'submit','class'=>'btn btn-success confirm',
                    'data-confirm'=>trans('Are you sure you want to publish this contract?')])!!}
                    {!!Form::close()!!}
                    <button data-toggle="modal" data-target=".metadata-reject-modal" class="btn btn-danger">Reject
                    </button>

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
                                    <h4 class="modal-title" id="myModalLabel">@lang('Please enter your comment')</h4>
                                </div>
                                <div class="modal-body">
                                    {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                                    'style'=>'width:100%'])!!}
                                    {!!Form::hidden('type', 'metadata')!!}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default"
                                            data-dismiss="modal">@lang('contract.close')</button>
                                    <button type="submit"
                                            class="btn btn-primary">@lang('contract.save_changes')</button>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                @endif
            @elseif($contract->metadata_status == Contract::STATUS_REJECTED)
                @lang('Rejected')
                <a href="#" data-toggle="modal" data-target=".metadata-modal"><i
                            class="glyphicon glyphicon-pushpin"></i></a>

                <div class="modal fade metadata-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">@lang('Comment')</h4>
                            </div>
                            <div class="modal-body">
                                {!!nl2br($contract->metadata_comment->message)!!}


                                <hr style="margin-top: 50px;"/>
                                <p style="font-size: 13px;"><strong>Commented
                                        by</strong> {{$contract->metadata_comment->user->name}}
                                    on {{$contract->metadata_comment->user->created_at->format('D F d, h:i A')}}</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">@lang('contract.close')</button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                @lang('Draft')
                @if($current_user->hasRole('superadmin') || $current_user->can('complete-metadata') )
                    {!!Form::open(['route'=>['contract.status', $contract->id], 'style'=>"display:inline",
                    'method'=>'post'])!!}
                    {!!Form::hidden('state', 'completed')!!}
                    {!!Form::hidden('type', 'metadata')!!}
                    {!!Form::button(trans('Make Complete'), ['type'=>'submit','class'=>'btn btn-primary confirm',
                    'data-confirm'=>trans('Are you sure you want to marked complete this contract ?')])!!}
                    {!!Form::close()!!}
                @endif
            @endif
        </li>
        @if($status === $contract_completed)
            <li><strong>@lang('PDF Text'):</strong>
                @if($contract->text_status == Contract::STATUS_PUBLISHED)
                    @lang('Published')
                @elseif($contract->text_status == Contract::STATUS_COMPLETED)
                    @lang('Completed')
                    @if($current_user->hasRole('superadmin') || $current_user->can('publish-text') )
                        {!!Form::open(['route'=>['contract.status', $contract->id], 'style'=>"display:inline",
                        'method'=>'post'])!!}
                        {!!Form::hidden('state', 'published')!!}
                        {!!Form::hidden('type', 'text')!!}
                        {!!Form::button(trans('Publish'), ['type'=>'submit','class'=>'btn btn-success confirm',
                        'data-confirm'=>trans('Are you sure you want to publish this contract?')])!!}
                        {!!Form::close()!!}
                        <button data-toggle="modal" data-target=".text-rejectddd-modal" class="btn btn-danger">Reject</button>

                        <div class="modal fade text-rejectddd-modal" tabindex="-1" role="dialog"
                             aria-labelledby="text-rejectddd-modal"
                             aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                                    'class'=>'suggestion-form']) !!}
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                    aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title" id="myModalLabel">@lang('Please enter your comment')</h4>
                                    </div>
                                    <div class="modal-body">
                                        {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12,
                                        'style'=>'width:100%'])!!}
                                        {!!Form::hidden('type', 'text')!!}
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default"
                                                data-dismiss="modal">@lang('contract.close')</button>
                                        <button type="submit"
                                                class="btn btn-primary">@lang('contract.save_changes')</button>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    @endif
                @elseif($contract->text_status == Contract::STATUS_REJECTED)
                    @lang('Rejected')
                    <a href="#" data-toggle="modal" data-target=".text-reject-msg-modal"><i
                                class="glyphicon glyphicon-pushpin"></i></a>

                    <div class="modal fade text-reject-msg-modal" id="text-reject-msg-modal" tabindex="-1" role="dialog" aria-labelledby="text-reject-msg-modal"
                         aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">@lang('Comment')</h4>
                                </div>
                                <div class="modal-body">
                                    {!!nl2br($contract->text_comment->message)!!}

                                    <hr style="margin-top: 50px;"/>
                                    <p style="font-size: 13px;"><strong>Commented
                                            by</strong> {{$contract->text_comment->user->name}}
                                        on {{$contract->text_comment->user->created_at->format('D F d, h:i A')}}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default"
                                            data-dismiss="modal">@lang('contract.close')</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    @lang('Draft')
                    @if($current_user->hasRole('superadmin') || $current_user->can('complete-text') )
                        {!!Form::open(['route'=>['contract.status', $contract->id], 'style'=>"display:inline",
                        'method'=>'post'])!!}
                        {!!Form::hidden('state', 'completed')!!}
                        {!!Form::hidden('type', 'text')!!}
                        {!!Form::button(trans('Make Complete'), ['type'=>'submit','class'=>'btn btn-primary confirm',
                        'data-confirm'=>trans('Are you sure you want to marked complete this contract ?')])!!}
                        {!!Form::close()!!}
                    @endif
                @endif
            </li>
            <li><strong>@lang('Annotation'):</strong> @lang('Draft') </li>
        @else
            <li><strong>@lang('Text'):</strong></li>
            <li><strong>@lang('Annotation'):</strong></li>
        @endif
    </ul>
</div>