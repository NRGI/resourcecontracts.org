<?php
use App\Nrgi\Entities\Contract\Contract; ?>
@if($status == $contract_processing_completed)
<td>
    @if($contract->metadata_status == Contract::STATUS_PUBLISHED)
        <span class="state published">@lang('global.published')</span>
    @elseif($contract->metadata_status == Contract::STATUS_COMPLETED)
        <span class="state completed">@lang('global.completed')</span>
    @elseif($contract->metadata_status == Contract::STATUS_REJECTED)
        <span class="state completed">@lang('global.rejected')</span>
    @else
        <span class="state draft">@lang('global.draft')</span>
    @endif
</td>


<td>
    @if($contract->metadata_comment)
        <a href="#" data-toggle="modal" data-target=".metadata-modal"><i
                    class="glyphicon glyphicon-comment"></i></a>
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

    @if($contract->metadata_status == Contract::STATUS_COMPLETED)
        {!! Form::open(['route' => ['contract.status.comment', $contract->id],
        'class'=>'suggestion-form pull-left']) !!}
        {!!Form::hidden('type', 'metadata',[])!!}
        {!!Form::hidden('status', 'published' , [])!!}
        <button type="submit"
                class="btn btn-success metadata-status-comment">@lang("global.publish")</button>
        {!! Form::close() !!}

        <button data-toggle="modal" data-type="metadata" data-status="rejected" data-target=".status-modal" class="btn btn-danger metadata-status-comment">@lang("global.reject")
        </button>
    @endif
    @if($contract->metadata_status == Contract::STATUS_DRAFT)
            {!! Form::open(['route' => ['contract.status.comment', $contract->id],
            'class'=>'suggestion-form pull-left']) !!}
            {!!Form::hidden('type', 'metadata',[])!!}
            {!!Form::hidden('status', 'completed' , [])!!}
            <button type="submit"
                    class="btn btn-info metadata-status-comment">@lang("global.complete")</button>
            {!! Form::close() !!}

    @endif

</td>


<td>

    <?php
    $link = "http://www.".env('RC_LINK')."/contract/".$contract->metadata->open_contracting_id;
    if(in_array('olc',$contract->metadata->category))
    {
        $link = "http://www.".env('OLC_LINK')."/contract/".$contract->metadata->open_contracting_id;
    }
    ?>


        @if($elementState['metadata']=='published')
        @if(!empty($publishedInformation['metadata']['created_at']))
                {{$publishedInformation['metadata']['created_at']}} @lang('global.by') {{$publishedInformation['metadata']['user_name']}} .
        @endif
            <a href="{{$link}}" target="_blank"><span class="glyphicon glyphicon-link" title="@lang('global.check_metadata_in_subsite')"></span></a>
             <button data-toggle="modal" data-type="metadata" data-status="unpublished" data-target=".status-modal" class="btn btn-danger metadata-status-comment">@lang("global.unpublish")
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










