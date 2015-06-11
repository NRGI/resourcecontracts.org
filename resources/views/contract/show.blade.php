@extends('layout.app')

@section('content')
    <style>
        ul {
            list-style: none
        }
        ul li {padding: 5px 0px}
    </style>
    <div class="panel panel-default">
        <div class="panel-heading">{{$contract->metadata->project_title}}</div>

        <div class="action-btn pull-right" style="padding: 20px;">
            <a href="{{route('contract.edit', $contract->id)}}" class="btn btn-default">Edit</a>
            <a target="_blank" href="{{$file}}" class="btn btn-default">View Document</a>
            {!!Form::open(['route'=>['contract.destroy', $contract->id], 'style'=>"display:inline",
            'method'=>'delete'])!!}
            {!!Form::button('Delete', ['type'=>'submit','class'=>'btn btn-danger confirm', 'data-confirm'=>"Are you sure
            you want to delete this contract?"])!!}
            {!!Form::close()!!}
        </div>

        @if($status === \App\Nrgi\Services\Contract\ContractService::CONTRACT_COMPLETE)
            <div style="padding: 40px;">
                <a href="{{route('contract.pages', ['id'=>$contract->id])}}" class="btn btn-default">View Pages</a>
                <a href="{{route('contract.annotations.create', ['id'=>$contract->id])}}"
                   class="btn btn-default">Annotate</a>
            </div>
        @else
            <p style="padding: 20px 40px;">Status : {{$status==0 ? 'Pipeline' : 'Processing'}}</p>
        @endif

        <ul>
            <li><strong>File size:</strong> {{getFileSize($contract->metadata->file_size)}}</li>
            <li><strong>Created date:</strong> {{$contract->created_datetime->format('D M, d Y')}}</li>
            <li><strong>Updated date:</strong> {{$contract->last_updated_datetime->format('D M, d Y h:i A')}}</li>
            @if(isset($contract->metadata->language) && '' != $contract->metadata->language)
                <li><strong>Language:</strong> {{$contract->metadata->language}}</li>
            @endif
            @if(isset($contract->metadata->country->name) && '' != $contract->metadata->country->name)
                <li><strong>Country:</strong> {{$contract->metadata->country->name or ''}}</li>
            @endif
            @if(isset($contract->metadata->country->code) && '' != $contract->metadata->country->code)
                <li><strong>Country ISO:</strong> {{$contract->metadata->country->code or ''}}</li>
            @endif

            @if(is_array($contract->metadata->resource) && count($contract->metadata->resource)>0)
                <li><strong>Resource: </strong>{{join(', ', $contract->metadata->resource)}}</li>
            @endif

            @if(isset($contract->metadata->government_entity) && '' != $contract->metadata->government_entity)
                <li><strong>Government entity:</strong> {{$contract->metadata->government_entity}}</li>
            @endif
            @if(isset($contract->metadata->type_of_mining_title) && '' != $contract->metadata->type_of_mining_title)
                <li><strong>Type of mining title:</strong> {{$contract->metadata->type_of_mining_title}}</li>
            @endif
            @if(isset($contract->metadata->signature_date) && '' != $contract->metadata->signature_date)
                <li><strong>Signature date:</strong> {{$contract->metadata->signature_date}}</li>
            @endif
            @if(isset($contract->metadata->signature_year) && '' != $contract->metadata->signature_year)
                <li><strong>Signature year:</strong> {{$contract->metadata->signature_year}}</li>
            @endif
            @if(isset($contract->metadata->contract_term) && '' != $contract->metadata->contract_term)
                <li><strong>Contract term:</strong> {{$contract->metadata->contract_term}}</li>
            @endif

            @if(isset($contract->metadata->company))
                <?php $companies = array_filter($contract->metadata->company);?>
                @if(count($companies)>0)
                    <li><h3>Company</h3>
                        @foreach($companies as $k => $v)
                            <p> Company Name : {{$v->name}}</p>
                            <p> Jurisdiction Of Incorporation : {{$v->jurisdiction_of_incorporation}}</p>
                            <p> Registration Agency : {{$v->registration_agency}}</p>
                            <p> Incorporation Date : {{$v->company_founding_date}}</p>
                            <p> Company Address : {{$v->company_address}}</p>
                            <p> Company Role : {{$v->company_role}}</p>
                            <p> Identifier at company register: {{$v->comp_id}}</p>
                            <p> Parent company: {{$v->parent_company}}</p>
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

            @if(isset($contract->metadata->category) && is_array($contract->metadata->category) && count($contract->metadata->category)>0)
                <li><strong>Category:</strong> {{join(', ', $contract->metadata->category)}}</li>
            @endif
        </ul>
        @if($status === \App\Nrgi\Services\Contract\ContractService::CONTRACT_COMPLETE)
            <div class="annotation-wrap">
                <h3>Annotations</h3>

                <div class="annotation-list">
                    <ul>
                        @foreach($annotations as $annotation)
                            <li>
                                <span><a href="{{route('contract.annotations.create', ['id'=>$contract->id])}}?page={{$annotation->document_page_no}}"> {{$annotation->annotation->text}}</a></span>

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