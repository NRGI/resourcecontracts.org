@extends('layout.app')

<?php
$contract_processing_completed = \App\Nrgi\Entities\Contract\Contract::PROCESSING_COMPLETE;
$contract_processing_failed = \App\Nrgi\Entities\Contract\Contract::PROCESSING_FAILED;
$contract_processing_running = \App\Nrgi\Entities\Contract\Contract::PROCESSING_RUNNING;
$contract_processing_pipline = \App\Nrgi\Entities\Contract\Contract::PROCESSING_PIPELINE;
?>
@section('css')
    <link href="{{asset('css/bootstrap-editable.css')}}" rel="stylesheet"/>
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <style>
        textarea.form-control.input-large {
            width: 500px !important;
        }

        .input-sm {
            width: 400px !important;
        }

        .editable-click, a.editable-click, a.editable-click:hover {
            text-decoration: none;
            border-bottom: none !important;
        }
    </style>
@endsection

@section('script')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{asset('js/bootstrap-editable.min.js')}}"></script>
    <script>
        $(function () {
            $('body').on('hidden.bs.modal', '.modal-comment', function (event) {
                var modal = $(this);
                modal.removeData('bs.modal');
            });

            $('body').on('show.bs.modal', '.modal-comment', function (event) {
                var modal = $(this);
                modal.find('.modal-content').html('<div style="padding: 40px;"> Loading...</div>');
            });

            $.fn.editable.defaults.mode = 'inline';
            $('.edit-annotation-text').on('click', function () {
                        $(this).editable();
                    }
            );

            $('.annotation-delete-btn').on('click', function (e) {
                        e.preventDefault();
                        if (!confirm("{{_l("annotation.delete_confirm")}}")) {
                            return;
                        }
                        $(this).parent().fadeOut('slow');
                        var id = $(this).data('pk');
                        var url = app_url + "/api/annotation/" + id + "/delete";
                        $.ajax({
                            url: url,
                            type: "POST",
                            data: {'id': id},
                            success: function (data) {
                                $(this).parent().remove();
                            },
                            error: function () {
                                $(this).parent().fadeIn('slow');
                            }
                        });
                    }
            );

            $('.edit-annotation-category').on('click', function () {
                        var categories = {!!json_encode(trans("codelist/annotation.annotation_category"))!!};
                        $(this).editable({
                            source: categories,
                            select2: {
                                width: 400,
                                placeholder: 'Select category',
                                allowClear: true
                            }
                        });
                    }
            );
            <?php $pages = [];?>
            @for ($i = 1; $i <= $contract->pages()->count(); $i++)
            <?php $pages[$i] = $i; ?>
            @endfor
        $('.edit-annotation-page').on('click', function () {
                        var pages = {!!json_encode($pages)!!};
                        $(this).editable({
                            source: pages,
                            select2: {
                                width: 400,
                                placeholder: 'Select pages',
                                allowClear: true
                            }
                        });
                    }
            );
            var form = $('.output-type-form');

            $(form).on('submit', function (e) {
                e.preventDefault();
                var type = form.find('input[type=radio]:checked').val();
                if (typeof type != 'undefined') {
                    $.ajax({
                        url: form.attr('action'),
                        data: form.serialize(),
                        type: 'POST',
                        dataType: 'json'
                    }).done(function (response) {
                        window.location.reload()
                    })
                }
                else {
                    alert('Please select text type');
                }
            });


            var suggestion_form = $('.suggestion-form');
            $(suggestion_form).on('submit', function (e) {
                var text = $(this).find('#message').val();
                var status = $(this).find('#status').val();
                if (text == '' && status == 'rejected') {
                    e.preventDefault();
                    alert('Suggestion message is required.');
                    return false;
                }
                else {
                    $(this).find('input[type=submit]').text('loading...');
                    $(this).find('input[type=submit]').attr('disabled', 'disabled');
                    return true;
                }
            });
        })
    </script>
@stop

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$contract->title}}</div>
        <div class="action-btn pull-right" style="padding: 20px;">
            <a href="{{route('activitylog.index')}}?contract={{$contract->id}}"
               class="btn btn-default">@lang('activitylog.activitylog')</a>
            <a href="{{route('contract.edit', $contract->id)}}" class="btn btn-default">@lang('contract.edit')</a>

            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                    @lang('Download') <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a target="_blank" href="{{$contract->file_url}}">@lang('PDF')</a></li>
                    @if($contract->word_file !='')
                        <li><a href="{{route('contract.download', $contract->id)}}">@lang('Word')</a></li>
                    @endif
                </ul>
            </div>

            @if($current_user->can('delete-contract'))
                {!!Form::open(['route'=>['contract.destroy', $contract->id], 'style'=>"display:inline",
                'method'=>'delete'])!!}
                {!!Form::button(trans('contract.delete'), ['type'=>'submit','class'=>'btn btn-danger confirm',
                'data-confirm'=>trans('contract.confirm_delete')])!!}
                {!!Form::close()!!}
            @endif
        </div>

        @if($status == $contract_processing_completed)
            <div style="padding: 20px 20px 0px;">
                <a href="{{route('contract.review', ['id'=>$contract->id])}}"
                   class="btn btn-default">@lang('contract.view_pages')</a>
                <a href="{{route('contract.annotate', ['id'=>$contract->id])}}"
                   class="btn btn-default">@lang('contract.annotate_contract')</a>
                <br>
                <br>

                <p>
                    <strong>@lang('contract.show_pdf_text')
                        :</strong> @if(isset($contract->metadata->show_pdf_text) && $contract->metadata->show_pdf_text ==1) @lang('global.yes') @else @lang('global.no') @endif
                </p>

                @if($contract->pdf_structure != null)
                    <p>
                        <strong>@lang('contract.pdf_type')</strong> {{ucfirst($contract->pdf_structure)}}
                    </p>
                @endif
                <p><strong>@lang('contract.text_type'):</strong>
                    <a href="#" data-key="{{$contract->textType}}" class="text-type-block"
                       data-toggle="modal"
                       data-target=".text-type-modal">
                        @if($contract->textType =='')
                            @lang('contract.choose')

                        @else
                            <?php $label = $contract->getTextType();?>
                            <span class="label label-{{$label->color}}"> {{$label->name}}</span>
                        @endif
                    </a>

                    @if($contract->textType == 3 && is_null($contract->mturk_status))
                        {!! Form::open(['route' => ['mturk.add', $contract->id], 'method' => 'post']) !!}
                        {!! Form::button(trans('Send to Manual Transcription tasks'), ['type' =>'submit', 'class' =>'btn
                        btn-default confirm', 'data-confirm'=>'Are you sure you want to send this contract toMechanical Turk?']) !!}
                        {!! Form::close() !!}
                    @endif

                    @if($contract->mturk_status  == \App\Nrgi\Entities\Contract\Contract::MTURK_SENT)
                        @lang('Sent to MTurk') <a class="btn btn-default"
                                                  href="{{route('mturk.tasks', $contract->id)}}">@lang('View')</a>
                    @endif

                    @if($contract->mturk_status  == \App\Nrgi\Entities\Contract\Contract::MTURK_COMPLETE)
                        @lang('MTurk task Completed')
                    @endif
                </p>

                <div class="modal fade text-type-modal" id="text-type-modal" tabindex="-1" role="dialog"
                     aria-labelledby="text-type-modal"
                     aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            {!! Form::open(['route' => ['contract.output.save', $contract->id],
                            'class'=>'output-type-form']) !!}
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="myModalLabel">Choose type of output Text</h4>
                            </div>
                            <div class="modal-body">
                                <ul class="types">
                                    <li><label class="label label-success"> {!!Form::radio('text_type', 1,
                                            ($contract->textType == 1) ) !!}
                                            @lang('contract.acceptable')</label>
                                    <li><label class="label label-warning">{!!Form::radio('text_type', 2,
                                            ($contract->textType == 2)) !!} @lang('contract.needs_editing')
                                        </label>
                                    <li><label class="label label-danger">{!!Form::radio('text_type', 3,
                                            ($contract->textType == 3))
                                            !!} @lang('contract.needs_full_transcription')</label>
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default"
                                        data-dismiss="modal">@lang('contract.close')</button>
                                <button type="submit" class="btn btn-primary">@lang('contract.save_changes')s</button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        @elseif($status == $contract_processing_failed)
            <div class="status">@lang('contract.status'): @lang('Failed')
                (@lang('contract.fail_status', ['status'=>$contract->pdf_structure]))
            </div>
        @elseif($status== $contract_processing_running)
            <div class="status">@lang('contract.status'): @lang('Processing')</div>
        @elseif($status == $contract_processing_pipline)
            <div class="status">@lang('contract.status'): @lang('Pipeline')</div>
        @endif


        @include('contract.state')
        <a style="margin-left: 15px;margin-bottom: 25px" class="btn btn-default"
           href="{{route('contract.comment.list',$contract->id)}}">View all comments</a>

        <ul class="contract-info">
            <li><strong>@lang('Open Contracting ID'):</strong>
                {{$contract->metadata->open_contracting_id or ''}}
            </li>


            <li><strong>@lang('contract.created_by'):</strong>
                {{$contract->created_user->name}} on {{$contract->created_datetime->format('D M d, Y h:i A')}} (GMT)
            </li>

            @if(!is_null($contract->updated_user))
                <li><strong>@lang('contract.last_modified_by'):</strong> {{$contract->updated_user->name}}
                    on {{$contract->last_updated_datetime->format('D M d, Y h:i A')}} (GMT)
                </li>
            @endif

            <li>
                <strong>@lang('contract.contract_name'):</strong> {{$contract->metadata->contract_name or ''}}
                {!! discussion($discussions,$discussion_status, $contract->id,'contract_name','metadata') !!}
            </li>

            <li>
                <strong>@lang('contract.contract_identifier')
                    :</strong> {{$contract->metadata->contract_identifier or ''}}
                {!! discussion($discussions,$discussion_status, $contract->id,'contract_identifier','metadata') !!}
            </li>

            @if(isset($contract->metadata->annexes_missing))
                <li>
                    <strong>@lang('contract.annexes_display'):</strong>
                    @if($contract->metadata->annexes_missing == 1)Yes
                    @elseif($contract->metadata->annexes_missing == 0)No
                    @elseif($contract->metadata->annexes_missing == -1)Not Available
                    @endif
                    {!! discussion($discussions,$discussion_status, $contract->id,'annexes_missing','metadata') !!}
                </li>
            @endif

            @if(isset($contract->metadata->pages_missing))
                <li>
                    <strong>@lang('contract.pages_display'):</strong>
                    @if( $contract->metadata->pages_missing == 1)Yes
                    @elseif($contract->metadata->pages_missing == 0)No
                    @elseif($contract->metadata->annexes_missing == -1)Not Available
                    @endif
                    {!! discussion($discussions,$discussion_status, $contract->id,'pages_missing','metadata') !!}
                </li>
            @endif

            @if(isset($contract->metadata->language))
                <li>
                    <strong>@lang('contract.language'):</strong> {{getLanguageName($contract->metadata->language)}}
                    [{{$contract->metadata->language}}]
                    {!! discussion($discussions,$discussion_status, $contract->id,'language','metadata') !!}
                </li>
            @endif

            @if(isset($contract->metadata->country->name))
                <li>
                    <strong>@lang('contract.country'):</strong> {{$contract->metadata->country->name or ''}}
                    [{{$contract->metadata->country->code or ''}}]
                    @if(isset(config('amla')[$contract->metadata->country->code]))
                        <a href="{{config('amla')[$contract->metadata->country->code]}}">@lang('contract.amla')</a>
                    @endif
                    {!! discussion($discussions,$discussion_status, $contract->id,'country','metadata') !!}
                </li>
            @endif

            <li>
                <strong>@lang('contract.resource'): </strong>
                @if(is_array($contract->metadata->resource) && count($contract->metadata->resource)>0)
                    {{join(', ', $contract->metadata->resource)}}
                @endif
                {!! discussion($discussions,$discussion_status, $contract->id,'resource','metadata') !!}
            </li>

            @if(isset($contract->metadata->government_entity))

                <div class="government-entity-wrap license-wrap">
                    @foreach($contract->metadata->government_entity as $key => $governmentEntity)
                        <li>
                            <strong>@lang('contract.government_entity'):</strong> {{$governmentEntity->entity or ''}}
                            {!! discussion($discussions,$discussion_status, $contract->id,'entity-'.$key,'metadata') !!}
                        </li>
                        <li>
                            <strong>@lang('contract.government_identifier'):</strong> {{$governmentEntity->identifier or ''}}
                            {!! discussion($discussions,$discussion_status, $contract->id,'identifier-'.$key,'metadata') !!}
                        </li>
                    @endforeach
                </div>
            @endif

            <li><strong>@lang('contract.type_of_contract'): </strong>
                @if(is_array($contract->metadata->type_of_contract) && count($contract->metadata->type_of_contract)>0)
                    {{join(', ', $contract->metadata->type_of_contract)}}
                @endif
                {!! discussion($discussions,$discussion_status, $contract->id,'type_of_contract','metadata') !!}
            </li>

            <li>
                <strong>@lang('contract.signature_date'):</strong> {{$contract->metadata->signature_date or ''}}
                {!! discussion($discussions,$discussion_status, $contract->id,'signature_date','metadata') !!}
            </li>

            <li>
                <strong>@lang('contract.document_type'):</strong> {{$contract->metadata->document_type or ''}}
                {!! discussion($discussions,$discussion_status, $contract->id,'document_type','metadata') !!}
            </li>


            @if(isset($contract->metadata->company))
                <?php $companies = $contract->metadata->company;?>
                @if(count($companies)>0)
                    <li><h3>@lang('contract.company')</h3>
                        @foreach($companies as $k => $v)
                            <div style="margin-bottom: 20px; border-bottom:1px solid #ccc; padding-bottom:20px; ">
                                <p>
                                    <strong>@lang('contract.company_name'):</strong>{{$v->name}}
                                    {!! discussion($discussions,$discussion_status, $contract->id,'name-'.$k,'metadata') !!}
                                </p>

                                @if(isset($v->participation_share))
                                    <p>
                                        <strong>@lang('contract.participation_share'):</strong> {{$v->participation_share}}
                                        {!! discussion($discussions,$discussion_status, $contract->id,'participation_share-'.$k,'metadata') !!}
                                    </p>
                                @endif

                                <p>
                                    <strong>@lang('contract.jurisdiction_of_incorporation'):</strong> {{@trans('codelist/country')[$v->jurisdiction_of_incorporation]}}
                                    {!! discussion($discussions,$discussion_status, $contract->id,'jurisdiction_of_incorporation-'.$k,'metadata') !!}
                                </p>

                                <p>
                                    <strong>@lang('contract.registry_agency'):</strong> {{$v->registration_agency}}
                                    {!! discussion($discussions,$discussion_status, $contract->id,'registration_agency-'.$k,'metadata') !!}
                                </p>

                                <p>
                                    <strong>@lang('contract.incorporation_date'):</strong> {{$v->company_founding_date}}
                                    {!! discussion($discussions,$discussion_status, $contract->id,'company_founding_date-'.$k,'metadata') !!}
                                </p>

                                <p>
                                    <strong>@lang('contract.company_address'):</strong> {{$v->company_address}}
                                    {!! discussion($discussions,$discussion_status, $contract->id,'company_address-'.$k,'metadata') !!}
                                </p>

                                <p>
                                    <strong>@lang('contract.company_number'):</strong> @if(isset($v->company_number)){{$v->company_number}}@endif
                                    {!! discussion($discussions,$discussion_status, $contract->id,'company_number-'.$k,'metadata') !!}
                                </p>

                                <p>
                                    <strong>@lang('contract.corporate_grouping'):</strong>@if(isset($v->parent_company)) {{$v->parent_company}}@endif
                                    {!! discussion($discussions,$discussion_status, $contract->id,'parent_company-'.$k,'metadata') !!}
                                </p>
                                <p>
                                    <strong>@lang('contract.open_corporate'):</strong> @if(!empty($v->open_corporate_id)) <a target="_blank"
                                                                                                                             href="{{$v->open_corporate_id}}">{{$v->open_corporate_id}}</a>@endif
                                    {!! discussion($discussions,$discussion_status, $contract->id,'open_corporate_id-'.$k,'metadata') !!}
                                </p>
                                @if(isset($v->operator))
                                    <p>
                                        <strong>@lang('contract.operator'):</strong>@if($v->operator==1)Yes @elseif($v->operator==0) No @elseif($v->operator==-1) Not Available @endif
                                        {!! discussion($discussions,$discussion_status, $contract->id,'operator-'.$k,'metadata') !!}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </li>
                @endif
            @endif

            <li><h3>@lang('contract.license_and_project')</h3></li>
            @if(isset($contract->metadata->concession))
                <div class="license-wrap">
                    @foreach($contract->metadata->concession as $key => $concession)
                        @if(isset($concession->license_name))
                            <li>
                                <strong>@lang('contract.license_name_only'):</strong>
                                {{$concession->license_name}}
                                {!! discussion($discussions,$discussion_status, $contract->id,'license_name-'.$key,'metadata') !!}
                            </li>
                        @endif
                        @if(isset($concession->license_identifier))
                            <li>
                                <strong>@lang('contract.license_identifier_only'):</strong>
                                {{$concession->license_identifier}}
                                {!! discussion($discussions,$discussion_status, $contract->id,'license_identifier-'.$key,'metadata') !!}
                            </li>
                        @endif

                    @endforeach
                </div>
            @endif
            <li>
                <strong>@lang('contract.project_name'):</strong> {{$contract->metadata->project_title or ''}}
                {!! discussion($discussions,$discussion_status, $contract->id,'project_title','metadata') !!}
            </li>
            <li>
                <strong>@lang('contract.project_identifier'):</strong> {{$contract->metadata->project_identifier or ''}}
                {!! discussion($discussions,$discussion_status, $contract->id,'project_identifier','metadata') !!}
            </li>
            <li><h3>@lang('contract.source')</h3></li>
            <li>
                <strong>@lang('contract.source_url'):</strong> <a href="{{$contract->metadata->source_url}}">{{$contract->metadata->source_url}}</a>
                {!! discussion($discussions,$discussion_status, $contract->id,'source_url','metadata') !!}
            </li>
            <li>
                <strong>@lang('contract.disclosure_mode'):</strong> {{$contract->metadata->disclosure_mode or ''}}
                {!! discussion($discussions,$discussion_status, $contract->id,'disclosure_mode','metadata') !!}
            </li>
            <li>
                <strong>@lang('contract.date_of_retrieval'):</strong> {{$contract->metadata->date_retrieval}}
                {!! discussion($discussions,$discussion_status, $contract->id,'date_retrieval','metadata') !!}
            </li>
            <li>
                <strong>@lang('contract.category'):</strong>
                <?php $catConfig = config('metadata.category');?>

                @if(isset($contract->metadata->category) && is_array($contract->metadata->category) && count($contract->metadata->category)>0)
                    <?php $cat = [];
                    foreach ($contract->metadata->category as $key):
                        $cat[] = $catConfig[$key];
                    endforeach;
                    ?>
                    {{join(', ', $cat)}}
                @endif
                {!! discussion($discussions,$discussion_status, $contract->id,'category','metadata') !!}
            </li>

            @if(in_array('olc' , $contract->metadata->category))

                <li>
                    <strong>{{ trans('contract.deal_number') }}:</strong>
                    @if(isset($contract->metadata->deal_number))
                        {{ $contract->metadata->deal_number }}
                    @endif
                    {!! discussion($discussions,$discussion_status, $contract->id,'deal_number','metadata') !!}
                </li>
                <li>
                    <strong>{{ trans('contract.matrix_page') }}:</strong>
                    @if(isset( $contract->metadata->matrix_page))
                        <a href="{{ $contract->metadata->matrix_page }}" target="_blank">{{$contract->metadata->matrix_page}}</a>
                    @endif
                    {!! discussion($discussions,$discussion_status, $contract->id,'matrix_page','metadata') !!}
                </li>
            @endif

            <li>
                <strong>{{trans('contract.contract_note')}}:</strong>
                @if(isset($contract->metadata->contract_note))
                    {{$contract->metadata->contract_note}}
                @endif
                {!! discussion($discussions,$discussion_status, $contract->id,'contract_note','metadata') !!}
            </li>

            <li><h3>@lang('contract.associated_contracts')</h3></li>
                @if(!empty($associatedContracts))
                    @foreach($associatedContracts as $associatedContract)
                        <li>
                            <a href="{{route('contract.show',$associatedContract['contract']['id'])}}">{{$associatedContract['contract']['contract_name']}} @if($associatedContract['parent'])(Main)@endif </a>
                        </li>
                    @endforeach
                @else
                    <li>There is no associated documents.</li>
                @endif
        </ul>
        @if($status == $contract_processing_completed)
            <div class="annotation-wrap" id="annotations">
                <h3>@lang('contract.annotations')<span>Double click to edit.</span></h3>

                <div class="annotation-list">
                    <ul>
                        @forelse($annotations as $annotation)
                            <li>
                                <a href="javascript:void(0)" data-pk="{{$annotation->id}}"
                                   class="annotation-delete-btn">delete</a>
                                @if(property_exists($annotation->annotation, "shapes"))
                                    <span class="annotation-type-icon annotation-pdf-icon"></span>
                                @else
                                    <span class="annotation-type-icon annotation-text-icon"></span>
                                @endif
                                <span data-pk="{{$annotation->id}}" data-name="category"
                                      data-url="{{route('annotation.update')}}"
                                      data-value="{{$annotation->annotation->category}}" data-type="select"
                                      class="edit-annotation-category">{{_l("codelist/annotation.annotation_category.{$annotation->annotation->category}")}}</span>

                                <p data-pk="{{$annotation->id}}" data-name="text"
                                   data-url="{{route('annotation.update')}}" data-type="textarea"
                                   class="edit-annotation-text">{{$annotation->annotation->text}}</p>
                                <p class="annotation-footer" style="margin-bottom: 10px;">
                                    @if(property_exists($annotation->annotation, "shapes"))
                                        <span style="clear: both; display: inline">
                                            <a href="{{route('contract.annotate', ['id'=>$contract->id])}}#/pdf/page/{{$annotation->document_page_no}}">{{$annotation->annotation->quote or 'pdf annotation'}} </a>
                                <p data-pk="{{$annotation->id}}" data-name="document_page_no"
                                   data-url="{{route('annotation.update')}}"
                                   data-value={{$annotation->document_page_no}} data-type="select" class="edit-annotation-page"> [Page {{$annotation->document_page_no}}]</p>
                                </span>
                                @else
                                    <span style="clear: both;  display: inline">
                                            <a href="{{route('contract.annotate', ['id'=>$contract->id])}}#/text/page/{{$annotation->document_page_no}}">{{$annotation->annotation->quote or 'text annotation'}} </a>
                                            [Page {{$annotation->document_page_no}}]
                                        </span>
                                @endif

                                @if(property_exists($annotation->annotation, 'tags'))
                                    @foreach($annotation->annotation->tags as $tag)
                                        <div>{{$tag}}</div>
                                        @endforeach
                                        @endif
                                        {!! discussion($discussions,$discussion_status, $contract->id,$annotation->id,'annotation') !!}
                                        </p>
                            </li>
                        @empty
                            <li>
                                @lang('Annotation not created. Please create')
                                <a style="font-size: 14px" href="{{route('contract.annotate', ['id'=>$contract->id])}}">here</a>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade modal-comment" id="commentModel" tabindex="-1" role="dialog" aria-labelledby="commentModelLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div style="padding: 40px;"> Loading...</div>
            </div>
        </div>
    </div>

@stop
