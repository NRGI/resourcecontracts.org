@extends('layout.app')
@section('css')
    <style>
        .col-sm-2 {
            background-color: #fff;
            padding: 25px 0px;
            border: 1px solid #EAEAEA;
        }
        .contract-list .media {margin-bottom: 20px;}
    </style>
@stop

@section('content')
    <div class="panel panel-default" style="background-color: #F5F5F5;">
        <div class="row" style="  padding: 5px; text-align: center; }">
            <div class="col-xs-6 col-sm-2 col-md-offset-1">
                <h4>Total Contract</h4>
                <span class="text-muted">{{$stats['total'] or 0}}</span>
            </div>
            <div class="col-xs-6 col-sm-2">
                <h4>Last Month</h4>
                <span class="text-muted">{{$stats['last_month'] or 0}}</span>
            </div>
            <div class="col-xs-6 col-sm-2">
                <h4>This Month</h4>
                <span class="text-muted">{{$stats['this_month'] or 0}}</span>
            </div>
            <div class="col-xs-6 col-sm-2">
                <h4>Yesterday</h4>
                <span class="text-muted">{{$stats['yesterday'] or 0}}</span>
            </div>
            <div class="col-xs-6 col-sm-2">
                <h4>Today</h4>
                <span class="text-muted">{{$stats['today'] or 0}}</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-6 col-sm-4">
            <h4>Metadata status</h4>
            <ul>
                <li>
                    Published: <span class="number number-published">{{$status['metadata']['published'] or 0}}</span>
                </li>
                <li>
                    Completed: <span class="number number-completed">{{$status['metadata']['completed'] or 0}}</span>
                </li>
                <li>
                    Draft: <span class="number number-draft">{{$status['metadata']['draft'] or 0}}</span>
                </li>
                <li>
                    Rejected: <span class="number number-rejected">{{$status['metadata']['rejected'] or 0}}</span>
                </li>
            </ul>
        </div>

        <div class="col-xs-6 col-sm-4">
            <h4>Annotations status</h4>

            <ul>
                <li>
                    Published: <span class="number number-published">{{$status['annotation']['published'] or 0}}</span>
                </li>
                <li>
                    Completed: <span class="number number-completed">{{$status['annotation']['completed'] or 0}}</span>
                </li>
                <li>
                    Draft: <span class="number number-draft">{{$status['annotation']['draft'] or 0}}</span>
                </li>
                <li>
                    Rejected: <span class="number number-rejected">{{$status['annotation']['rejected'] or 0}}</span>
                </li>
                <li>
                    On processing: <span class="number number-published">{{$status['annotation']['processing'] or 0}}</span>
                </li>
            </ul>
        </div>

        <div class="col-xs-6 col-sm-4">
            <h4>Pdf Text status</h4>
            <ul>
                <li>
                    Published: <span class="number number-completed">{{$status['pdfText']['published'] or 0}}</span>
                </li>
                <li>
                    Completed: <span class="number number-completed">{{$status['pdfText']['completed'] or 0}}</span>
                </li>
                <li>
                    Draft: <span class="number number-draft">{{$status['pdfText']['draft'] or 0}}</span>
                </li>
                <li>
                    Rejected: <span class="number number-rejected">{{$status['pdfText']['rejected'] or 0}}</span>
                </li>
                <li>
                    On processing: <span class="number number-published">{{$status['pdfText']['processing'] or 0}}</span>
                </li>
            </ul>
        </div>
    </div>


    <div class="row">
        <div class="col-md-6">
            <div class="page-header">
                <h3>Most Recently added contracts </h3>
            </div>
            <div class="contract-list">
                @forelse($recent_contracts as $contract)

                <div class="media">
                    <p class="pull-right">
                        <small>{{$contract->created_datetime->diffForHumans()}}</small>
                    </p>
                    <div class="media-body">
                        <h4 class="media-heading user_name">{{$contract->metadata->contract_name or ''}}, {{$contract->metadata->country->name or ''}}, {{$contract->metadata->signature_year or ''}}</h4>
                        - {{$contract->created_user->name}}
                    </div>
                </div>
            @empty
                <div>@lang('Contract not created yet.')</div>
            @endforelse
            </div>

        </div>
    </div>

@stop
