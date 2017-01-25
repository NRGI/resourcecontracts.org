<?php
use App\Nrgi\Entities\Contract\Annotation\Annotation;
use App\Nrgi\Entities\Contract\Contract;
?>
@if($status == $contract_processing_completed)
    <td>
        @if(count($annotations) > 0)
            @if($annotationStatus == Annotation::PUBLISHED)
                <span class="state published">@lang('global.published')</span>
            @elseif($annotationStatus == Annotation::COMPLETED)
                <span class="state completed">@lang('global.completed')</span>
            @elseif($annotationStatus == Annotation::REJECTED)
                <span class="state completed">@lang('global.rejected')</span>
            @else
                <span class="state draft">@lang('global.draft')</span>
            @endif
        @else
            <a href="{{route('contract.annotate', ['id'=>$contract->id])}}"
               class="btn btn-default annotate">@lang('contract.annotate')</a>
            <br>
        @endif
    </td>

    <td>
        @if($contract->annotation_comment)
            <a href="#" data-toggle="modal" data-target=".annotation-reject-msg-modal"><i
                        class="glyphicon glyphicon-comment"></i></a>

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

    </td>

    <td>
        @if($elementState['metadata']=='published')

            @if($annotationStatus == Annotation::COMPLETED)
                {!! Form::open(['route' => ['contract.annotations.status', $contract->id],
                'class'=>'suggestion-form pull-left']) !!}
                {!!Form::hidden('type', 'annotation')!!}
                {!!Form::hidden('current-status', $annotationStatus)!!}
                {!!Form::hidden('status','published', ['id'=>"status"])!!}
                <button type="submit" class="btn btn-success annotation-status-comment">@lang("global.publish")
                </button>
                {!! Form::close() !!}

                <button data-toggle="modal" data-status="rejected" data-target=".annotation-comment-modal" class="btn btn-danger annotation-status-comment">@lang("global.reject")
                </button>
            @endif
            @if($annotationStatus == Annotation::DRAFT)
                    {!! Form::open(['route' => ['contract.annotations.status', $contract->id],
                    'class'=>'suggestion-form pull-left']) !!}
                    {!!Form::hidden('type', 'annotation')!!}
                    {!!Form::hidden('current-status', $annotationStatus)!!}
                    {!!Form::hidden('status','completed', ['id'=>"status"])!!}
                    <button type="submit" class="btn btn-info annotation-status-comment">@lang("global.complete")
                    </button>
                    {!! Form::close() !!}
            @endif
        @endif

    </td>

    <td>
        <?php
        $link = "http://".env('RC_LINK')."/contract/".$contract->metadata->open_contracting_id."#annotations";
        if (in_array('olc', $contract->metadata->category)) {
            $link = "http://".env('OLC_LINK')."/contract/".$contract->metadata->open_contracting_id."#annotations";
        }
        ?>

        @if( $elementState['metadata']=='published' && $elementState['annotation']=='published')
            @if(!empty($publishedInformation['annotation']['created_at']))
                {{$publishedInformation['annotation']['created_at']}} @lang('global.by') {{$publishedInformation['annotation']['user_name']}} .
            @endif
            <a href="{{$link}}" target="_blank"><span class="glyphicon glyphicon-link" title="@lang('global.check_annotation_in_subsite')"></span></a>

            <button data-toggle="modal" data-status="unpublished" data-target=".annotation-comment-modal"
                         class="btn btn-danger annotation-status-comment re">@lang("global.unpublish")
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

