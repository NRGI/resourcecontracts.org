@extends('layout.app')
@section('css')
    <style>
        .col-sm-2 {
            background-color: #fff;
            padding: 25px 0px;
            border: 1px solid #EAEAEA;
        }

        .contract-list .media {
            margin-bottom: 20px;
        }
        .row-dashboard ul {padding:0px;}
    </style>
@stop

@section('content')
    <div class="panel panel-default panel-dashboard">
        <div class="row dashboard-top-wrapper">
            <div class="col-sm-10 col-lg-10">
                <h3>Contracts Uploaded</h3>
                <div class="contract-upload-wrap">
                <div class="col-xs-6 col-sm-3">
                    <h4>Last Month</h4>
                    <span class="text-muted">{{$stats['last_month'] or 0}}</span>
                </div>
                <div class="colm-xs-6 col-sm-3">
                    <h4>This Month</h4>
                    <span class="text-muted">{{$stats['this_month'] or 0}}</span>
                </div>
                <div class="col-xs-6 col-sm-3">
                    <h4>Yesterday</h4>
                    <span class="text-muted">{{$stats['yesterday'] or 0}}</span>
                </div>
                <div class="col-xs-6 col-sm-3">
                    <h4>Today</h4>
                    <span class="text-muted">{{$stats['today'] or 0}}</span>
                </div>
                </div>
            </div>
            <div class="col-xs-6 col-sm-2 col-lg-2 col-md-offset-1">
                <h4>Total Contracts</h4>
                <span class="text-muted">{{$stats['total'] or 0}}</span>
            </div>

        </div>
    </div>

    <div class="row row-dashboard">
        <div class="col-xs-6 col-sm-3">
            <h4>Metadata status</h4>
            <ul>
                <li>
                    <a href="{{route('contract.index',["type"=>"metadata","status"=>"published"])}}">Published:</a>  <span class="number number-published">{{$status['metadata']['published'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"metadata","status"=>"completed"])}}">Completed:</a> <span class="number number-completed">{{$status['metadata']['completed'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"metadata","status"=>"draft"])}}">Draft:</a> <span class="number number-draft">{{$status['metadata']['draft'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"metadata","status"=>"rejected"])}}">Rejected:</a> <span class="number number-rejected">{{$status['metadata']['rejected'] or 0}}</span>
                </li>
            </ul>
        </div>

        <div class="col-xs-6 col-sm-3">
            <h4>Annotations status</h4>

            <ul>
                <li>
                    <a href="{{route('contract.index',["type"=>"annotations","status"=>"published"])}}">Published:</a> <span class="number number-published">{{$status['annotation']['published'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"annotations","status"=>"completed"])}}">Completed:</a><span class="number number-completed">{{$status['annotation']['completed'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"annotations","status"=>"draft"])}}">Draft: </a><span class="number number-draft">{{$status['annotation']['draft'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"annotations","status"=>"rejected"])}}">Rejected:</a> <span class="number number-rejected">{{$status['annotation']['rejected'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"annotations","status"=>"processing"])}}"> Not Available:</a> <span
                            class="number number-published">{{$status['annotation']['processing'] or 0}}</span>
                </li>
            </ul>
        </div>

        <div class="col-xs-6 col-sm-3">
            <h4>Pdf Text status</h4>
            <ul>
                <li>
                    <a href="{{route('contract.index',["type"=>"pdftext","status"=>"published"])}}">Published: </a><span class="number number-completed">{{$status['pdfText']['published'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"pdftext","status"=>"completed"])}}"> Completed: </a><span class="number number-completed">{{$status['pdfText']['completed'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"pdftext","status"=>"draft"])}}"> Draft:</a> <span class="number number-draft">{{$status['pdfText']['draft'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"pdftext","status"=>"rejected"])}}"> Rejected:</a> <span class="number number-rejected">{{$status['pdfText']['rejected'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"pdftext","status"=>"null"])}}"> On processing: </a><span
                            class="number number-published">{{$status['pdfText']['processing'] or 0}}</span>
                </li>
            </ul>
        </div>
        <div class="col-xs-6 col-sm-3">
            <h4>OCR</h4>
            <ul>
                <li>
                    <a href="{{route('contract.index',["type"=>"ocr","status"=>"1"])}}">Acceptable:</a>  <span class="number number-published">{{$ocrStatusCount['acceptable'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"ocr","status"=>"2"])}}">Needs Editing:</a> <span class="number number-completed">{{$ocrStatusCount['editing'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"ocr","status"=>"3"])}}">Needs Full Transcription:</a> <span class="number number-draft">{{$ocrStatusCount['transcription'] or 0}}</span>
                </li>
                <li>
                    <a href="{{route('contract.index',["type"=>"ocr","status"=>"null"])}}">Not Available:</a> <span class="number number-draft">{{$ocrStatusCount['non'] or 0}}</span>
                </li>

            </ul>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-12">
            <div class="page-header">
                <h3>Most Recently added contracts </h3>
            </div>
            <table class="table table-responsive table-dashboard">
                @forelse($recent_contracts as $contract)
                    <tr>
                        <td>
                            <h5 class="media-heading user_name">{{$contract->metadata->contract_name or ''}}
                                , {{$contract->metadata->country->name or ''}}
                                , {{$contract->metadata->signature_year or ''}}</h5>
                            <span>- {{$contract->created_user->name}}</span>
                        </td>
                        <td style="width: 160px;">
                            <small>{{$contract->created_datetime->format('M d. Y \a\t H:i A')}}</small>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">@lang('Contract not created yet.')</td>
                    </tr>
                @endforelse
            </table>

        </div>
    </div>

@stop
