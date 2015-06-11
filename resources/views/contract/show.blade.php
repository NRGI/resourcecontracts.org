@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$contract->metadata->project_title}}</div>

        <div class="action-btn pull-right" style="padding: 20px;">
            {{--<a href="{{route('contract.edit', $contract->id)}}" class="btn btn-default">Edit</a>--}}
            <a target="_blank" href="{{$file}}" class="btn btn-default">View Document</a>
            {!!Form::open(['route'=>['contract.destroy', $contract->id], 'style'=>"display:inline",
            'method'=>'delete'])!!}
            {!!Form::button('Delete', ['type'=>'submit','class'=>'btn btn-danger confirm', 'data-confirm'=>"Are you sure
            you want to delete this contract?"])!!}
            {!!Form::close()!!}
        </div>

        @if($status === \App\Nrgi\Services\Contract\ContractService::CONTRACT_COMPLETE)
            <a href="{{route('contract.pages', ['id'=>$contract->id])}}" class="btn btn-default">View Pages</a>
            <a href="{{route('contract.annotations.create', ['id'=>$contract->id])}}"
               class="btn btn-default">Annotate</a>
        @else
            <p style="padding: 20px;;">Status : {{$status==0 ? 'Pipeline' : 'Processing'}}</p>
        @endif

        <table class="table">

            <tr>
                <td>Created date</td>
                <td>{{$contract->created_datetime->format('D M, d Y')}}</td>
            </tr>

            <tr>
                <td>Updated date</td>
                <td>{{$contract->last_updated_datetime->format('D M, d Y h:i A')}}</td>
            </tr>

            @foreach($contract->metadata as $key => $value)
                @if($key == 'file_size')
                    <tr>
                        <td width="20%">{{ ucfirst(str_replace('_', ' ',$key))}}</td>
                        <td>{{getFileSize($value)}}</td>
                    </tr>
                @elseif(!is_array($value))
                    <tr>
                        <td width="20%">{{ ucfirst(str_replace('_', ' ',$key))}}</td>
                        <td>{{$value}}</td>
                    </tr>

                @elseif($key =='resource')
                    <tr>
                        <td width="20%">{{ ucfirst(str_replace('_', ' ',$key))}}</td>
                        <td>{{join(', ',$value)}}</td>
                    </tr>

                @elseif($key == 'company')
                    @foreach($value as $k => $v)
                        <tr>
                            <td width="20%">{{ ucfirst(str_replace('_', ' ',$key))}}</td>
                            <td>
                                <p> Company Name : {{$v->name}}</p>

                                <p> Jurisdiction Of Incorporation : {{$v->jurisdiction_of_incorporation}}</p>

                                <p> Registration Agency : {{$v->registration_agency}}</p>

                                <p> Incorporation Date : {{$v->company_founding_date}}</p>

                                <p> Company Address : {{$v->company_address}}</p>

                                <p> Company Role : {{$v->company_role}}</p>

                                <p> Identifier at company register: {{$v->comp_id}}</p>

                                <p> Parent company: {{$v->parent_company}}</p>
                            </td>
                        </tr>
                    @endforeach


                @endif
            @endforeach
        </table>

        @if($status === \App\Nrgi\Services\Contract\ContractService::CONTRACT_COMPLETE)
            <div class="annotation-wrap">
                <h3>Annotations</h3>

                <div class="annotation-list">
                    <ul>
                        @foreach($annotations as $annotation)
                            <li>
                                <span>{{$annotation->annotation->text}}</span>

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