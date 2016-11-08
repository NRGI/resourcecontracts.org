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
            {!! Form::open(['route' => ['contract.import.confirm.post',$import_key], 'method' => 'post', 'files'=>true]) !!}
            <table class="table">
                <thead>
                <tr>
                    <td></td>
                    <th>@lang('contract.import.sn')</th>
                    <th>@lang('contract.import.contract_title')</th>
                    <th>@lang('contract.import.status_name')</th>
                    <th>@lang('contract.import.remarks')</th>
                </tr>
                </thead>

                <tbody>
                @foreach($contracts as $key => $contract)
                    <tr class="contract-{{$contract->id}}">
                        <td>
                            <input type="checkbox" name="id[]"
                            @if($contract->download_status == \App\Nrgi\Services\Contract\ImportService::COMPLETED)
                              checked="checked"
                            @else
                              disabled="disabled"
                            @endif
                              value="{{$contract->id}}">
                        </td>
                        <td>{{$contract->id}}</td>
                        <td>{{$contract->metadata->contract_name}}</td>
                        <td class="status">
                            @if($contract->download_status == \App\Nrgi\Services\Contract\ImportService::PIPELINE)
                                Pipeline
                            @endif
                            @if($contract->download_status == \App\Nrgi\Services\Contract\ImportService::PROCESSING)
                                Processing
                            @endif
                            @if($contract->download_status == \App\Nrgi\Services\Contract\ImportService::COMPLETED)
                                Ready to import
                            @endif
                            @if($contract->download_status == \App\Nrgi\Services\Contract\ImportService::FAILED)
                                Failed
                            @endif
                        </td>
                        <td class="remarks">{!!$contract->download_remarks or ''!!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>


            <div class="form-action">
                <div class="col-sm-12">
                    {!! Form::submit(trans('contract.submit'),[ 'disabled'=>'disabled', 'class'=>'btn btn-confirm-submit btn-lg btn-primary']) !!}
                    {!! Form::close() !!}

                    {!!Form::open(['route'=>['contract.import.delete', $import_key], 'style'=>"display:inline",
                    'method'=>'delete'])!!}
                    {!!Form::button(trans('contract.import.upload_another'), ['type'=>'submit', 'class'=>'btn btn-delete btn-primary'])!!}
                    {!!Form::close()!!}
                </div>
            </div>

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
                        $(el_id).find('.status').html(getStatusText(val.download_status));
                        $(el_id).find('.remarks').html(val.download_remarks);
                        if(val.download_status == 2)
                        {
                            $(el_id).find("input").removeAttr('disabled').attr('checked','checked');
                        }
                    });

                    if(isAllProcessCompleted(data.contracts))
                    {
                        tracker = false;
                        $('.btn-confirm-submit').removeAttr('disabled');
                    }
                    else
                    {
                        $('.btn-confirm-submit').attr('disabled', 'disabled');
                    }
                });
            }

            function isAllProcessCompleted(data)
            {
                var total = data.length;
                var process_completed = 0;
                $.each( data, function( key, val ) {
                    if(val.download_status > 1)
                    {
                        process_completed++;
                    }
                });

                return total == process_completed;
            }

            function getStatusText(status)
            {
                if(status == 0)
                {
                    return 'Pipeline';
                }
                if(status == 1)
                {
                    return 'Processing';
                }
                if(status == 2)
                {
                    return 'Ready to import';
                }
                if(status == 3)
                {
                    return 'Failed';
                }
            }
        })
    </script>

@stop
