@extends('layout.app')

@section('content')
    <div class="panel panel-default">

        <div class="panel-heading">@lang('activitylog.all_activitylog')</div>
        <div class="panel-body">
            <table class="table table-responsive">
                <tbody>
                @forelse($activityLogs as $activitylog)
                    <tr>
                        <td>{{ $activitylog->contract->metadata->contract_name }}</td>
                        <td>{{ trans($activitylog->message,$activitylog->message_params) }}</td>
                        <td align="right">@lang('by') {{$activitylog->user->name}} @lang('on') {{$activitylog->created_at->format('D F d, Y h:i a')}}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">@lang('activitylog.not_found')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            {!!$activityLogs->render()!!}
        </div>
    </div>
@endsection
