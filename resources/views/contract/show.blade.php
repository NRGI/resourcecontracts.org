@extends('layout.app-full')
@section('content')
    <div class="document-view-wrapper">
        <div class="top-document-wrapper">
            <div class="left-document-wrapper">
                <h1>{{$contract->metadata->project_title}}</h1>

                <div class="modify-wrapper">
                    Modified on
                    <div class="date">{{$contract->last_updated_datetime->format('D F d Y')}}</div>
                    <div class="language-wrap">
                        <div class="language">{{$contract->metadata->language}}</div>
                    </div>
                </div>
            </div>
            <div class="right-document-wrapper">
                <div class="sector-wrap">
                    <span class="sector">Resource(s)</span>
                    <ul>
                        <li><a href="#">{{$contract->metadata->resource}}</a></li>
                    </ul>
                </div>
                <div class="country-year-wrap">
                    <div class="country">
                        <span>Country</span>

                        <div class="country-name">{{$contract->metadata->country}}</div>
                    </div>
                    <div class="signature-year">
                        <span>Year of Signature</span>

                        <div class="year">{{$contract->metadata->signature_date}}</div>
                    </div>
                </div>
            </div>
            <div class="action-btn">
                {!!Form::open(['route'=>['contract.destroy', $contract->id], 'method'=>'delete'])!!}
                {!!Form::button('', ['type'=>'submit','class'=>'remove confirm', 'data-confirm'=>"Are you sure you want to delete this contract?"])!!}
                {!!Form::close()!!}
                <a href="{{route('contract.edit', $contract->id)}}" class="edit">Edit</a>
            </div>
        </div>

        @if($status === \App\Nrgi\Services\Contract\ContractService::CONTRACT_COMPLETE)
            <div class="view-wrapper">
                <div class="view-links">
                    <a href="{{route('contract.pages', ['id'=>$contract->id])}}" class="btn">View Pages</a>
                    <a href="#" class="btn">Annotate</a>
                </div>
            </div>
            <div class="annotation-wrap">
                <h3>Annotations</h3>

                <div class="annotation-list">
                    <ul>
                        <li>
                            <span>Lorem Ipsum</span>

                            <p>raft (Godwin) Shelley</p>
                            <a href="#">ERRATA</a>
                        </li>
                        <li>
                            <span>Test</span>

                            <p>days perpetually occupied</p>
                            <a href="#">SUBRATLLAT</a>
                        </li>
                        <li>
                            <span>Lorem Ipsum</span>

                            <p>raft (Godwin) Shelley</p>
                            <a href="#">ERRATA</a>
                        </li>
                    </ul>
                </div>
            </div>
        @else
            <div class="view-wrapper">
                <div class="status-wrap">
                    <div class="status-icon">
                        <img src="{{asset('images/ic_hourglass.png')}}" alt=" {{$status}}" />
                    </div>
                    {{$status==0 ? 'Pipeline' : 'Processing'}}
                </div>
            </div>
        @endif
    </div>
@stop