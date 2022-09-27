<table class="table table-striped table-responsive">
    <thead>
    <tr>
        <th width="40%">@lang('mturk.contract_name')</th>
        <th>@lang('mturk.category')</th>
        <th>@lang('mturk.created_on')</th>
        <th>@lang('mturk.sent_to_rc_on')</th>
        <th>@lang('mturk.tasks')</th>
    </tr>
    </thead>
    <tbody>
    @forelse($contracts as $contract)
        <tr>
            <td><a href="{{route('mturk.tasks',['contract_id' => $contract->id])}}">{{$contract->title}}</a></td>
            <td>{{strtoupper($contract->metadata->category[0])}}</td>
            <td>
                @if($contract->mturk_created_at)
                    {{translate_date($contract->mturk_created_at->format('M d, Y'))}}
                @endif

                <Br/> @lang('mturk.by') {{$contract->mturk_created_by}}

            </td>
            <td>
                @if($contract->mturk_sent_at)
                    {{translate_date($contract->mturk_sent_at->format('M d, Y'))}}
                @endif
                <Br/>   @lang('mturk.by') {{translate_date($contract->mturk_sent_by)}}
            </td>
            <td>{{$contract->tasks->count()}}</td>
        </tr>
    @empty
        <tr>
            <td colspan="10">@lang('mturk.not_found')</td>
        </tr>
    @endforelse
    </tbody>
</table>

@if ($contracts->lastPage()>1)
    <div class="text-center">
        <div class="pagination-text">
            @lang('contract.showing') {{($contracts->currentPage()==1)?"1":($contracts->currentPage()-1)*$contracts->perPage()}} @lang('contract.to') {{($contracts->currentPage()== $contracts->lastPage())?$contracts->total():($contracts->currentPage())*$contracts->perPage()}} @lang('contract.of') {{$contracts->total()}} @lang('contract.contract')
        </div>
        {!! $contracts->appends($app->request->all())->render() !!}
    </div>
@endif
