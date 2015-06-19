@extends('layout.app')

@section('css')
    <style>
        .select2 {width: 20% !important; float: left; margin-right: 20px !important; margin-top: 4px !important;}
    </style>
@stop
@section('content')
    <div class="panel panel-default">

        <div class="panel-heading">@lang('contract.all_contract') <a href="{{route('contract.create')}}"
                                                    class="pull-right btn btn-primary">@lang('contract.add')</a></div>
        <div class="panel-body">
            {!! Form::open(['route' => 'contract.index', 'method' => 'get', 'class'=>'form-inline']) !!}
            {!! Form::select('year', ['all'=>trans('contract.year')] + $years , Input::get('year') , ['class' => 'form-control']) !!}
            {!! Form::select('country', ['all'=>trans('contract.country')] + $countries , Input::get('country') , ['class' =>
            'form-control']) !!}
            {!! Form::submit(trans('contract.search'), ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
            <br/>
            <br/>
            <table class="table table-responsive">
                @forelse($contracts as $contract)
                    <tr>
                        <td width="70%">
                            <i class="glyphicon glyphicon-file"></i>
                            <a href="{{route('contract.show', $contract->id)}}">{{$contract->metadata->contract_name or $contract->metadata->project_title}}</a>
                            <span class="label label-default"><?php echo $contract->metadata->language;?></span>
                        </td>
                        <td align="right">{{getFileSize($contract->metadata->file_size)}}</td>
                        <td align="right">{{$contract->created_datetime->format('F d, Y')}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">@lang('contract.contract_not_found')</td>
                    </tr>
                @endforelse

            </table>
        </div>
    </div>
@endsection
@section('script')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script type="text/javascript">
        $('select').select2({placeholder: "Select", allowClear: true, theme: "classic"});
    </script>
@stop