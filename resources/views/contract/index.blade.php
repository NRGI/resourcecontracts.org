@extends('layout.app')

@section('css')
    <style>
        .select2 {
            width: 14% !important;
            float: left;
            margin-right: 20px !important;
            margin-top: 4px !important;
        }

        .btn-import {
            margin-left: 100px;
        }
    </style>
@stop
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('contract.all_contract')

            <div class="btn-group pull-right" role="group" aria-label="...">
                <?php
                    $url=Request::all();
                    $url['download']=1;
                  ?>
                <a href="{{route('bulk.text.download')}}" class="btn btn-info" style="margin-right: 20px" download>@lang('global.text_download')</a>
                <a href="{{route("contract.index",$url)}}" class="btn btn-info" style="margin-right: 20px">@lang('contract.download')</a>
                <a href="{{route('contract.import')}}" class="btn btn-default">@lang('contract.import.name')</a>
                <a href="{{route('contract.select.type')}}" class="btn btn-primary btn-import">@lang('contract.add')</a>
            </div>
        </div>

        <div class="panel-body">
           {!! Form::open(['route' => 'contract.index', 'method' => 'get', 'class'=>'form-inline']) !!}
            {!! Form::select('year', ['all'=>trans('contract.year')] + $years , Input::get('year') , ['class' =>
            'form-control']) !!}

            {!! Form::select('country', ['all'=>trans('contract.country')] + $countries , Input::get('country') ,
            ['class' =>'form-control']) !!}

            {!! Form::select('category', ['all'=>trans('contract.category')] + config('metadata.category'),
            Input::get('category') ,
            ['class' =>'form-control']) !!}

            {!! Form::select('resource', ['all'=>trans('contract.resource')] + trans_array($resources,
            'codelist/resource') ,
            Input::get
            ('resource') ,
            ['class' =>'form-control']) !!}
            {!! Form::text('q', Input::get('q') , ['class' =>'form-control','placeholder'=>trans('contract.search_contract')]) !!}

            {!! Form::submit(trans('contract.search'), ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
            <br/>
            <br/>
            <table class="table table-contract table-responsive">
                @forelse($contracts as $contract)
                    <tr>
                        <td width="70%">
                            <i class="glyphicon glyphicon-file"></i>
                            <a href="{{route('contract.show', $contract->id)}}">{{$contract->metadata->contract_name or $contract->metadata->project_title}}</a>
                            <span class="label label-default"><?php echo $contract->metadata->language;?></span>
                        </td>
                        <td align="right">{{getFileSize($contract->metadata->file_size)}}</td>
                        <td align="right"><?php echo $contract->createdDate('M d, Y');?></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">@lang('contract.contract_not_found')</td>
                    </tr>
                @endforelse

            </table>
            @if ($contracts->lastPage()>1)
                <div class="text-center paginate-wrapper">
                    <div class="pagination-text">@lang('contract.showing') {{($contracts->currentPage()==1)?"1":($contracts->currentPage()-1)*$contracts->perPage()}} @lang('contract.to') {{($contracts->currentPage()== $contracts->lastPage())?$contracts->total():($contracts->currentPage())*$contracts->perPage()}} @lang('contract.of') {{$contracts->total()}} @lang('contract.contract')</div>
                    {!! $contracts->appends($app->request->all())->render() !!}
                </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script type="text/javascript">
        var lang_select = '@lang('global.select')';
        $('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
    </script>
@stop