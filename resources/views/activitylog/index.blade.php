@extends('layout.app')

@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet"/>
    <style>
        .select2 {
            width: 20% !important;
            float: left;
            margin-right: 20px !important;
            margin-top: 4px !important;
        }

        .filter {
            float: left;
            margin-right: 10px;
            margin-top: 7px
        }
    </style>
@stop

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('activitylog.activitylog')</div>

        <div class="panel-body">
            {!! Form::open(['route' => 'activitylog.index', 'method' => 'get', 'class'=>'form-inline']) !!}
            <label class="filter">@lang('activitylog.filterby')</label>
            {!! Form::select('contract', ['all'=>trans('activitylog.all_contract')] + $contracts, Input::get('contract')
            ,
            ['class' =>'form-control']) !!}

            {!! Form::select('user', ['all'=>trans('activitylog.all_user')] + $users , Input::get('user') ,
            ['class' =>'form-control']) !!}

            {!! Form::submit(trans('contract.search'), ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}
            <br/>
            <br/>

            <table class="table table-striped table-responsive">
                <thead>
                <tr>
                    <th width="40%">
                    @lang('activitylog.contract')</td>
                    <th>
                    @lang('activitylog.action')</td>
                </tr>
                </thead>
                <tbody>

                @forelse($activityLogs as $activitylog)
                    <tr>
                        <td><a href="{{route('contract.show',$activitylog->contract_id)}}">{{ $activitylog->contract->metadata->contract_name or ''}}</a></td>
                        <td>
                            {{ trans($activitylog->message,$activitylog->message_params) }} <br>
                            @lang('global.by') {{$activitylog->user->name}} @lang('global.on')
                            <?php echo $activitylog->createdDate('F d, Y \a\t h:i A');?>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">@lang('activitylog.not_found')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            {!!$activityLogs->appends(Input::all())->render()!!}
        </div>
    </div>
@endsection

@section('script')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script>
        var lang_select = '@lang('global.select')';
        $(function () {
            $('select').select2({placeholder: lang_select, allowClear: true, theme: "classic"});
        });
    </script>
@stop

