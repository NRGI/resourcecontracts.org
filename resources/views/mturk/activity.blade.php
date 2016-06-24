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
        <div class="panel-heading">@lang('activitylog.activitylog')
            <a class="btn btn-default pull-right" href="{{route('mturk.index')}}">@lang('mturk.back')</a>
        </div>

        <div class="panel-body">
            {!! Form::open(['route' => 'mturk.activity', 'method' => 'get', 'class'=>'form-inline']) !!}
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
                        <th width="40%">@lang('activitylog.contract')</th>
                        <th>@lang('mturk.page_no')</th>
                        <th>@lang('activitylog.action')</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td><a href="{{route('contract.show',$activity->contract_id)}}">{{ $activity->contract->metadata->contract_name or ''}}</a></td>
                        <td>{{ $activity->page_no or ''}}</td>

                        <td>
                            {{ trans($activity->message,$activity->message_params) }} <br>
                            @lang('by') {{$activity->user->name}} @lang('mturk.on') {{$activity->created_at->format('D F d, Y h:i a')}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">@lang('activitylog.not_found')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            {!!$activities->appends(Input::all())->render()!!}
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

