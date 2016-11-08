@extends('layout.app')

@section('css')
    <style>
        .remarks {width: 280px;}
    </style>
@stop

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('contract.import.status')</div>
        <div class="panel-body">
            <table class="table">
                <thead>
                <tr>
                    <th>@lang('contract.import.sn')</th>
                    <th>@lang('contract.import.contract_title')</th>
                    <th>@lang('contract.import.status_name')</th>
                    <th>@lang('contract.import.remarks')</th>
                </tr>
                </thead>

                <tbody>
                @foreach($contracts as $key => $contract)
                    <tr class="contract-{{$contract->id}}">
                        <td>{{$contract->id}}</td>
                        <td>{{$contract->metadata->contract_name}}</td>
                        <td class="status">
                            @if($contract->create_status == \App\Nrgi\Services\Contract\ImportService::CREATE_PENDING)
                                @lang('contract.import.pending')
                            @endif

                            @if($contract->create_status == \App\Nrgi\Services\Contract\ImportService::CREATE_PROCESSING)
                                  @lang('contract.import.processing')
                            @endif

                            @if($contract->create_status == \App\Nrgi\Services\Contract\ImportService::CREATE_COMPLETED)
                                  @lang('contract.import.completed')
                            @endif

                            @if($contract->create_status == \App\Nrgi\Services\Contract\ImportService::CREATE_FAILED)
                                  @lang('contract.import.failed')
                            @endif
                        </td>
                        <td class="remarks">{!!$contract->create_remarks or ''!!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('script')

    <script>
        var import_json = '{{$import_json or ''}}';
        $(function(){

            var tracker = true;
            import_tracker();

            setInterval(import_tracker, 2000);

            function import_tracker()
            {
                if(!tracker) return false;

                $.getJSON(import_json, function(data){
                    $.each( data.contracts, function( key, val ) {
                        var el_id = 'tr.contract-'+ val.id;
                        $(el_id).find('.status').html(getStatusText(val.create_status));
                        $(el_id).find('.remarks').html(val.create_remarks);
                    });
                }).fail(function() {
                   window.location.reload();
                });
            }

            function getStatusText(status)
            {
                if(status == 0)
                {
                    return 'Pending';
                }

                if(status == 1)
                {
                    return 'Processing';
                }

                if(status == 2)
                {
                    return 'Completed';
                }

                if(status == 3)
                {
                    return 'Failed';
                }
            }
        })
    </script>

@stop
