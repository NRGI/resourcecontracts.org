<?php

use App\Nrgi\Entities\Contract\Contract; ?>


@if($status == $contract_processing_completed)
<td>
    @if($contract->text_status == Contract::STATUS_PUBLISHED)
        <span class="state published">@lang('global.published')</span>
    @elseif($contract->text_status == Contract::STATUS_COMPLETED)
        <span class="state completed">@lang('global.completed')</span>
    @elseif($contract->text_status == Contract::STATUS_DRAFT)
        <span class="state draft">@lang('global.draft')</span>
    @else
        <span class="state rejected">@lang('global.rejected')</span>
    @endif
</td>

<td>
    @if($contract->text_comment)
        <a href="#" data-toggle="modal" data-target=".text-reject-msg-modal"><i
                    class="glyphicon glyphicon-comment"></i></a>
        <div class="modal fade text-reject-msg-modal" id="text-reject-msg-modal" tabindex="-1" role="dialog"
             aria-labelledby="text-reject-msg-modal"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                    aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">@lang('global.all_comments_pdf')</h4>
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
                                    @lang('global.by') <strong>{{$text_comment->user->name}}</strong>
                                    @lang('global.on') {{$text_comment->created_at->format('D F d, Y h:i a')}}
                                </div>
                            </div>
                        @empty
                            <p> @lang('global.no_comment')</p>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        <a href="{{route('contract.comment.list',$contract->id)}}"
                           class="btn btn-default">@lang('global.view_all')</a>
                        <button type="button" class="btn btn-default"
                                data-dismiss="modal">@lang('contract.close')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</td>

<td>
    @if($elementState['metadata']=='published' )
        @if($contract->textType==1)
            @if($contract->text_status == Contract::STATUS_COMPLETED)
                {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                'class'=>'suggestion-form pull-left']) !!}
                {!!Form::hidden('type', 'text',[])!!}
                {!!Form::hidden('status', 'published' , [])!!}
                <button type="submit"
                        class="btn btn-success metadata-status-comment">@lang("global.publish")</button>
                {!! Form::close() !!}

                <button data-toggle="modal" data-type="text" data-status="rejected" data-target=".status-modal" class="btn btn-danger metadata-status-comment">@lang("global.reject")
                </button>
            @endif
            @if($contract->text_status == Contract::STATUS_DRAFT)
                    {!! Form::open(['route' => ['contract.status.comment', $contract->id],
                    'class'=>'suggestion-form pull-left']) !!}
                    {!!Form::hidden('type', 'text',[])!!}
                    {!!Form::hidden('status', 'completed' , [])!!}
                    <button type="submit"
                            class="btn btn-info metadata-status-comment">@lang("global.complete")</button>
                    {!! Form::close() !!}

                @endif
        @endif
    @endif


</td>

<td>
    <?php
    $link = "http://www.".env('RC_LINK')."/contract/".$contract->metadata->open_contracting_id."/view#text";
    if(in_array('olc',$contract->metadata->category))
    {
        $link = "http://www.".env('OLC_LINK')."/contract/".$contract->metadata->open_contracting_id."/view#text";
    }
    ?>
    @if($contract->metadata_status == Contract::STATUS_PUBLISHED && $elementState['text']=='published')
        @if(!empty($publishedInformation['text']['created_at']))
            {{$publishedInformation['text']['created_at']}} @lang('global.by') {{$publishedInformation['text']['user_name']}} .

        @endif
        <a href="{{$link}}" target="_blank"><span class="glyphicon glyphicon-link" title="@lang('global.check_text_in_subsite')"></span></a>
        <button data-toggle="modal" data-type="text" data-status="unpublished" data-target=".status-modal"  class="btn btn-danger metadata-status-comment">@lang("global.unpublish")
        </button>
    @else
        -
    @endif
</td>
@else
    <td></td>
    <td></td>
    <td></td>
    <td></td>
@endif


