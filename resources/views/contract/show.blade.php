@extends('layout.app')


@section('css')
    <style>
        ul {
            list-style: none
        }

        ul li {
            padding: 5px 0px
        }

        .types label {
            display: block;
            cursor: pointer;
            text-align: left;
            padding: 15px;
            font-size: 16px;
            width: 50%;
        }

        .label-red {
            background: #d9534f;
            font-size: 13px
        }

        .label-yellow {
            background: #f0ad4e;
            font-size: 13px
        }

        .label-green {
            background: #5cb85c;
            font-size: 13px
        }

    </style>
@stop


@section('script')
    <script>
        $(function () {
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
            })
        })
    </script>
@stop


@section('content')

    <div class="panel panel-default">
        <div class="panel-heading">{{$contract->metadata->project_title}}</div>

        <div class="action-btn pull-right" style="padding: 20px;">
            <a href="{{route('contract.edit', $contract->id)}}" class="btn btn-default">Edit</a>
            <a target="_blank" href="{{$file}}"
               class="btn btn-default">Download file [{{getFileSize($contract->metadata->file_size)}}]</a>
            {!!Form::open(['route'=>['contract.destroy', $contract->id], 'style'=>"display:inline",
            'method'=>'delete'])!!}
            {!!Form::button('Delete', ['type'=>'submit','class'=>'btn btn-danger confirm', 'data-confirm'=>"Are you sure
            you want to delete this contract?"])!!}
            {!!Form::close()!!}
        </div>

        @if($status === \App\Nrgi\Services\Contract\ContractService::CONTRACT_COMPLETE)
            <div style="padding: 40px;">
                <a href="{{route('contract.pages', ['id'=>$contract->id])}}" class="btn btn-default">View Pages</a>
                <a href="{{route('contract.annotations.index', ['id'=>$contract->id])}}"
                   class="btn btn-default">Annotate</a>
                <br>
                <br>

                <p>Text type :

                    <a href="#" data-key="{{$contract->textType}}" class="text-type-block"
                       data-toggle="modal"
                       data-target="#text-type-modal">
                        @if($contract->textType =='')
                            Choose

                        @else
                            <?php $label = $contract->getTextType();?>
                            <span class="label label-{{$label->color}}"> {{$label->name}}</span>
                        @endif

                    </a></p>

                <!-- Modal -->
                <div class="modal fade" id="text-type-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
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
                                            Acceptable</label>
                                    <li><label class="label label-warning">{!!Form::radio('text_type', 2,
                                            ($contract->textType == 2)) !!} Needs
                                            editing</label>
                                    <li><label class="label label-danger">{!!Form::radio('text_type', 3,
                                            ($contract->textType == 3)) !!} Needs
                                            full transcription</label>
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>


            </div>
        @else
            <p style="padding: 20px 40px;">Status : {{$status==0 ? 'Pipeline' : 'Processing'}}</p>
        @endif

        <ul>
            <li><strong>Created on:</strong> {{$contract->created_datetime->format('D M, d Y h:i A')}}</li>
            <li style="margin-bottom: 30px;"><strong>Updated
                    on:</strong> {{$contract->last_updated_datetime->format('D M, d Y h:i A')}}</li>

            @if(isset($contract->metadata->language) && '' != $contract->metadata->language)
                <?php $lang = config('metadata.language');?>
                <li>
                    <strong>Language:</strong> {{$lang[$contract->metadata->language]}}
                    [{{$contract->metadata->language}}]
                </li>
            @endif
            @if(isset($contract->metadata->country->name) && '' != $contract->metadata->country->name)
                <li>
                    <strong>Country:</strong> {{$contract->metadata->country->name or ''}}
                    [{{$contract->metadata->country->code or ''}}]
                </li>
            @endif

            @if(is_array($contract->metadata->resource) && count($contract->metadata->resource)>0)
                <li><strong>Resource: </strong>{{join(', ', $contract->metadata->resource)}}</li>
            @endif

            @if(isset($contract->metadata->government_entity) && '' != $contract->metadata->government_entity)
                <li><strong>Government entity:</strong> {{$contract->metadata->government_entity}}</li>
            @endif

            @if(isset($contract->metadata->government_identifier) && '' != $contract->metadata->government_identifier)
                <li><strong>Government identifier:</strong> {{$contract->metadata->government_identifier}}</li>
            @endif

            @if(isset($contract->metadata->type_of_contract) && '' != $contract->metadata->type_of_contract)
                <li><strong>Type of Contract:</strong> {{$contract->metadata->type_of_contract}}</li>
            @endif
            @if(isset($contract->metadata->signature_date) && '' != $contract->metadata->signature_date)
                <li><strong>Signature date:</strong> {{$contract->metadata->signature_date}}</li>
            @endif

            @if(isset($contract->metadata->document_type) && '' != $contract->metadata->document_type)
                <li><strong>Document Type:</strong> {{$contract->metadata->document_type}}</li>
            @endif

            @if(isset($contract->metadata->translation_from_original) && '' != $contract->metadata->translation_from_original)
                <li><strong>Translation from original:</strong>
                    @if($contract->metadata->translation_from_original ==1)
                        Yes [$contract->metadata->translation_parent]
                    @else
                        No
                    @endif
                </li>
            @endif

            @if(isset($contract->metadata->company))
                <?php $companies = array_filter($contract->metadata->company);?>
                @if(count($companies)>0)
                    <li><h3>Company</h3>
                        @foreach($companies as $k => $v)
                            <p><strong>Company Name:</strong>  {{$v->name}}</p>
                            <p><strong>Jurisdiction Of Incorporation :</strong> {{$v->jurisdiction_of_incorporation}}
                            </p>
                            <p><strong>Registration Agency :</strong> {{$v->registration_agency}}</p>
                            <p><strong>Incorporation Date :</strong> {{$v->company_founding_date}}</p>
                            <p><strong>Company Address :</strong> {{$v->company_address}}</p>
                            <p><strong>Identifier at company register:</strong> {{$v->comp_id}}</p>
                            <p><strong>Parent company:</strong> {{$v->parent_company}}</p>
                            <p><strong>Open Corporate ID:</strong> @if(!empty($v->open_corporate_id)) <a target="_blank"
                                                                                                         href="https://opencorporates.com/companies/{{$v->open_corporate_id}}">{{$v->open_corporate_id}}</a>@endif
                            </p>
                        @endforeach
                    </li>
                @endif
            @endif


            <li><h3>Concession / license / Project</h3></li>
            @if(isset($contract->metadata->license_name) && '' != $contract->metadata->license_name)
                <li><strong>License name:</strong> {{$contract->metadata->license_name}}</li>
            @endif
            @if(isset($contract->metadata->license_identifier) && '' != $contract->metadata->license_identifier)
                <li><strong>License identifier:</strong> {{$contract->metadata->license_identifier}}</li>
            @endif
            @if(isset($contract->metadata->license_source_url) && '' != $contract->metadata->license_source_url)
                <li><strong>License source url:</strong> {{$contract->metadata->license_source_url}}</li>
            @endif
            @if(isset($contract->metadata->license_type) && '' != $contract->metadata->license_type)
                <li><strong>License type:</strong> {{$contract->metadata->license_type}}</li>
            @endif
            @if(isset($contract->metadata->project_title) && '' != $contract->metadata->project_title)
                <li><strong>Project title:</strong> {{$contract->metadata->project_title}}</li>
            @endif
            @if(isset($contract->metadata->project_identifier) && '' != $contract->metadata->project_identifier)
                <li><strong>Project identifier:</strong> {{$contract->metadata->project_identifier}}</li>
            @endif
            @if(isset($contract->metadata->date_granted) && '' != $contract->metadata->date_granted)
                <li><strong>Date granted:</strong> {{$contract->metadata->date_granted}}</li>
            @endif
            @if(isset($contract->metadata->year_granted) && '' != $contract->metadata->year_granted)
                <li><strong>Year granted:</strong> {{$contract->metadata->year_granted}}</li>
            @endif
            @if(isset($contract->metadata->ratification_date) && '' != $contract->metadata->ratification_date)
                <li><strong>Date of ratification:</strong> {{$contract->metadata->ratification_date}}</li>
            @endif
            @if(isset($contract->metadata->ratification_year) && '' != $contract->metadata->ratification_year)
                <li><strong>Year of ratifciation:</strong> {{$contract->metadata->ratification_year}}</li>
            @endif

            <li><h3>Source</h3></li>
            @if(isset($contract->metadata->Source_url) && '' != $contract->metadata->Source_url)
                <li><strong>Source URL:</strong> {{$contract->metadata->Source_url}}</li>
            @endif
            @if(isset($contract->metadata->date_retrieval) && '' != $contract->metadata->date_retrieval)
                <li><strong>Date of retrieval:</strong> {{$contract->metadata->date_retrieval}}</li>
            @endif
            @if(isset($contract->metadata->location) && '' != $contract->metadata->location)
                <li><strong>Location:</strong> {{$contract->metadata->location}}</li>
            @endif

            <?php $catConfig = config('metadata.category');?>

            @if(isset($contract->metadata->category) && is_array($contract->metadata->category) && count($contract->metadata->category)>0)
                <li><strong>Category:</strong>
                    <?php $cat = [];
                    foreach($contract->metadata->category as $key):
                        $cat[] = $catConfig[$key];
                    endforeach;
                    ?>
                    {{join(', ', $cat)}}
                </li>
            @endif
        </ul>
        @if($status === \App\Nrgi\Services\Contract\ContractService::CONTRACT_COMPLETE)
            <div class="annotation-wrap">
                <h3>Annotations</h3>

                <div class="annotation-list">
                    <ul>
                        @foreach($annotations as $annotation)
                            <li>
                                <span><a href="{{route('contract.annotations.index', ['id'=>$contract->id])}}?page={{$annotation->document_page_no}}"> {{$annotation->annotation->text}}</a></span>

                                <p>{{$annotation->annotation->quote}}</p>
                                @foreach($annotation->annotation->tags as $tag)
                                    <a href="#">{{$tag}}</a>
                                @endforeach
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>
        @endif

    </div>
@stop