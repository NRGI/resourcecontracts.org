<?php
use App\Nrgi\Entities\Contract\Contract;

?>

<div class="annotation-wrap">
    <p> @lang('Contract State'):</p>
    <strong>@lang('Metadata'):</strong>


    @if($contract->status == Contract::STATUS_PUBLISHED)
        @lang('Published')
    @elseif($contract->status == Contract::STATUS_COMPLETED)
        @lang('Completed')
        @if($current_user->hasRole('superadmin') || $current_user->can('publish-contract') )
            {!!Form::open(['route'=>['contract.status', $contract->id], 'style'=>"display:inline",
            'method'=>'post'])!!}
            {!!Form::hidden('state', 'published')!!}
            {!!Form::button(trans('Publish it'), ['type'=>'submit','class'=>'btn btn-default confirm',
            'data-confirm'=>trans('Are you sure you want to publish this contract?')])!!}
            {!!Form::close()!!}
            <button data-toggle="modal" data-target="#suggest-it-modal" class="btn btn-default">Suggest it</button>

            <div class="modal fade" id="suggest-it-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
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
                            {!! Form::textarea('message', null, ['id'=>"message", 'rows'=>12, 'style'=>'width:100%'])!!}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal">@lang('contract.close')</button>
                            <button type="submit" class="btn btn-primary">@lang('contract.save_changes')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>


        @endif
    @elseif($contract->status == Contract::STATUS_REJECTED)
        @lang('Rejected')
          <a href="#" data-toggle="modal" data-target="#text-type-modal"><i class="glyphicon glyphicon-bookmark"></i></a>

            <div class="modal fade" id="text-type-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">@lang('Comment')</h4>
                        </div>
                        <div class="modal-body">

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
        @if($current_user->hasRole('superadmin') || $current_user->can('complete-contract') )
            {!!Form::open(['route'=>['contract.status', $contract->id], 'style'=>"display:inline",
            'method'=>'post'])!!}
            {!!Form::hidden('state', 'completed')!!}
            {!!Form::button(trans('Complete it'), ['type'=>'submit','class'=>'btn btn-default confirm',
            'data-confirm'=>trans('Are you sure you want to marked complete this contract ?')])!!}
            {!!Form::close()!!}
        @endif
    @endif
    <br/>

    @if($status === $contract_completed)
        <strong>@lang('Text'):</strong>  @lang('Draft') <br/>
        <strong>@lang('Annotation'):</strong> @lang('Draft') <br/>
    @else
        <strong>@lang('Text'):</strong>   <br/>
        <strong>@lang('Annotation'):</strong>  <br/>
    @endif
</div>