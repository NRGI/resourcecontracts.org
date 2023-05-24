<?php
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Entities\Contract\Annotation;

$annotationArray = $annotations->toArray();
$annotLastPublished   = isset($annotationArray[0]['last_published']) ? $annotationArray[0]['last_published'] : '';
?>
<div class="section-wrap">
    <div class="col-md-12">
        <div class="row clearfix">
            <div class="col-md-6 pull-left">
                @if($status == $contract_processing_completed)
                <strong class="text-quality pull-left">@lang('contract.text_quality'):</strong>
                <a href="#" data-key="{{$contract->textType}}" class="text-type-block pull-left" data-toggle="modal" data-target=".text-type-modal">
                    @if($contract->textType =='')
                        @lang('contract.choose')
                    @else
                        <?php $label = $contract->getTextType();?>
                        <span class="label btn pull-left label-{{$label->color}}"> @lang('contract.'.$label->name)
                            </span>
                    @endif
                </a>
              @endif

            <div class="modal fade text-type-modal pull-left" id="text-type-modal" tabindex="-1" role="dialog"  aria-labelledby="text-type-modal"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        {!! Form::open(['route' => ['contract.output.save', $contract->id],
                        'class'=>'output-type-form']) !!}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">@lang('contract.choose_text_type')</h4>
                        </div>
                        <div class="modal-body">
                            <ul class="types">
                                <li><label class="label label-success"> {!!Form::radio('text_type', 1,  ($contract->textType == 1) ) !!}
                                       <span>   @lang('contract.acceptable')</span>
                                    </label>
                                </li>
                                <li><label class="label label-warning">{!!Form::radio('text_type', 2,  ($contract->textType == 2)) !!}
                                        <span> @lang('contract.needs_editing')</span>
                                    </label>
                                </li>
                                <li><label class="label label-danger">{!!Form::radio('text_type', 3, ($contract->textType == 3))
                                                    !!}
                                       <span> @lang('contract.needs_full_transcription')</span>
                                    </label>
                                </li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger"
                                    data-dismiss="modal">@lang('contract.close')</button>
                            <button type="submit" class="btn btn-primary">@lang('contract.save_changes')</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
                <div style="margin-left: 100px; padding-top: 6px; clear: both; overflow: hidden">
                    @if($contract->textType == 3 && is_null($contract->mturk_status))
                        {!! Form::open(['route' => ['mturk.add', $contract->id], 'method' => 'post']) !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control full-width-textarea' ,'placeholder' => trans('mturk.hit_description'), 'rows' => '3' ]) !!}
                        {!! Form::textarea('qualification_id', null, ['class' => 'form-control full-width-textarea' ,'placeholder' => trans('mturk.write_qualification_id'), 'rows' => '3' ]) !!}
                        {!! Form::button(trans('Send to Mechanical Turk'), ['type' =>'submit', 'class' =>'btn
                        btn-gray confirm', 'data-confirm'=>'Are you sure you want to send this contract toMechanical Turk?']) !!}
                        {!! Form::close() !!}
                    @endif

                    @if($contract->mturk_status  == \App\Nrgi\Entities\Contract\Contract::MTURK_SENT)
                        @lang('global.mturk_sent') <a class="btn btn-gray"  href="{{route('mturk.tasks',['contract_id' => $contract->id])}}">@lang('global.view')</a>
                    @endif

                    @if($contract->mturk_status  == \App\Nrgi\Entities\Contract\Contract::MTURK_COMPLETE)
                        @lang('global.mturk_completed')
                    @endif
                </div>
            </div>
            <div class="state-wrap-title col-md-6 pull-right">
                @if(!$current_user->isCountryResearch())
                    @if($publishedInformation['metadata']['created_at'] !='' && $publishedInformation['text']['created_at']!='' && $publishedInformation['annotation']['created_at']!='' && $contract->textType == Contract::ACCEPTABLE)
                        {!!Form::open(['route'=>['contract.unpublish', $contract->id], 'style'=>"float:right;  padding-left: 10px;",
                        'method'=>'post'])!!}
                        {!!Form::hidden('metadata_status',$contract->metadata_status  )!!}
                        {!!Form::hidden('text_status', $contract->text_status )!!}
                        {!!Form::hidden('annotation_status', $annotationStatus )!!}
                        {!!Form::button(trans('contract.unpublish.all'), ['type'=>'submit','class'=>'btn btn-danger btn-sm confirm',
                        'data-confirm'=>trans('contract.unpublish.confirm')])!!}
                        {!!Form::close()!!}

                        {!!Form::open(['route'=>['contract.publish', $contract->id], 'style'=>"float:right",
                        'method'=>'post'])!!}
                        {!!Form::button(trans('contract.publish.all'), ['type'=>'submit','class'=>'btn btn-success btn-sm confirm',
                        'data-confirm'=>trans('contract.publish.confirm')])!!}
                        {!!Form::close()!!}
                    @endif
                @endif
            </div>
        </div>
    </div>

<div class="state-wrap">

    <table class="custom-table table">
        <thead>
        <th></th>
        <th>@lang('global.state')</th>
        <th width="80px">@lang('global.comment')</th>
        <th width="160px">@lang('global.action')</th>
        <th>@lang('global.published_info')</th>

        </thead>
        <tbody>
        <tr>
            <td>
                <strong>@lang('global.metadata'):</strong>
            </td>
            @include('contract.partials.show.metadata_status')
        </tr>
        <tr>
            <td>
                <strong>@lang('contract.pdf_text'):</strong>
            </td>
            @include('contract.partials.show.text_status')
        </tr>

        <tr>
            <td>
                <strong>@lang('annotation.annotation'):</strong>
            </td>
            @include('contract.partials.show.annotation_status')
        </tr>

        </tbody>
    </table>
    @include('contract.partials.show.modal.element_comment_modal')
    @include('contract.partials.show.modal.annotation_comment_modal')
</div>
</div>