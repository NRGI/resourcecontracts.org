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

<?php
use App\Nrgi\Entities\Contract\Contract;
?>

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
        <td>
            <a href="{{ route('mturk.tasks', ['contract_id' => $contract->id]) }}">{{ $contract->title }}</a>
            @if(isset($contract->metadata->countries) && is_array($contract->metadata->countries))
                - {{ implode(', ', array_map(function ($country) { return $country->name ?? ''; }, $contract->metadata->countries)) }}
            @endif
        </td>

            <td>{{strtoupper($contract->metadata->category[0])}}</td>
            <td>
                @if($contract->mturk_created_at)
                {{translate_date($contract->mturk_created_at->format('M d, Y'))}}
                @endif
                <br/>
            @lang('mturk.by') {{$contract->mturk_created_by}}  </td>
            <td class="number_center">{{$contract->tasks->count()}}</td>
            <td class="number_center">{{$contract->total_hits}}</td>
            <td class="number_center">{{$contract->count_status['total_completed']}}</td>
            <td class="number_center">{{$contract->count_status['total_approved']}}</td>
            @if($total_ra > 0)
                <td class="requiring_action">
                    <a href="{{route('mturk.tasks',['contract_id' => $contract->id])}}?status=1&approved=0">{{$total_ra}}</a>
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
                    @if($contract->mturk_status == 2 || $contract->metadata_status == Contract::STATUS_DRAFT)
                        <button class="btn btn-default" disabled="disabled">@lang('mturk.sent_to_rc')</button>
                    @else
                        {!! Form::open(['url' =>route('mturk.contract.copy',['contract_id' => $contract->id]), 'method' => 'post']) !!}
                        {!! Form::button(trans('mturk.send_to_rc'), ['type' =>'submit', 'class' => 'btn btn-success confirm', 'data-confirm'=>trans('mturk.sure_send_to_rc')])!!}
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

