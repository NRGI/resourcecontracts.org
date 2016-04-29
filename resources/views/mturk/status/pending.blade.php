@section('css')

    <style>
        .requiring_action {
            background-color: #FEFFB1;
            text-align: center;
            vertical-align: middle !important;
        }
        .number_center {
            text-align: center;
            vertical-align: middle !important;
        }
    </style>
@stop

<table class="table table-striped table-responsive">
    <thead>
    <tr>
        <th width="25%">@lang('mturk.contract_name')</th>
        <th>@lang('mturk.category')</th>
        <th>@lang('mturk.created_on')</th>
        <th>@lang('mturk.pages')</th>
        <th>@lang('mturk.tasks')</th>
        <th>@lang('mturk.completed')</th>
        <th>@lang('mturk.approved')</th>
        <th>@lang('mturk.requiring_action')</th>
        <th>@lang('mturk.rejected')</th>
        @if(!$current_user->isCountryResearch())
            <th>@lang('mturk.action_name')</th>
        @endif
    </tr>
    </thead>
    <tbody>
    @forelse($contracts as $contract)
        <?php $total_ra = $contract->count_status['total_completed'] - $contract->count_status['total_approved'] - $contract->count_status['total_rejected'];?>
        <tr>
            <td><a href="{{route('mturk.tasks', $contract->id)}}">{{$contract->title}}</a>
                - {{$contract->metadata->country->name}}
            </td>
            <td>{{strtoupper($contract->metadata->category[0])}}</td>
            <td>{{$contract->mturk_created_at}} <br/>
                By {{$contract->mturk_created_by}}  </td>
            <td class="number_center">{{$contract->tasks->count()}}</td>
            <td class="number_center">{{$contract->total_hits}}</td>
            <td class="number_center">{{$contract->count_status['total_completed']}}</td>
            <td class="number_center">{{$contract->count_status['total_approved']}}</td>
            @if($total_ra > 0)
                <td class="requiring_action">
                    <a href="{{route('mturk.tasks', $contract->id)}}?status=1&approved=0">{{$total_ra}}</a>
                </td>
            @else
                <td class="number_center">
                    {{$total_ra}}
                </td>
            @endif
            <td class="number_center">{{$contract->count_status['total_rejected']}}</td>
            @if(!$current_user->isCountryResearch())
            <td>
                @if($contract->tasks->count() == $contract->count_status['total_approved'])
                    @if($contract->mturk_status == 2)
                        <button class="btn btn-default" disabled="disabled">Sent to RC</button>
                    @else
                        {!! Form::open(['url' =>route('mturk.contract.copy',$contract->id), 'method' => 'post']) !!}
                        {!! Form::button(trans('Send to RC'), ['type' =>'submit', 'class' => 'btn btn-success confirm', 'data-confirm'=>'Are you sure you want to send text to RC?'])!!}
                        {!! Form::close() !!}
                    @endif
                @endif
            </td>
            @endif
        </tr>
    @empty
        <tr>
            <td colspan="10">@lang('mturk.not_found')</td>
        </tr>
    @endforelse
    </tbody>
</table>

