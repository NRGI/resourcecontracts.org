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
            {{ dd($contract) }}
            <td><a href="{{route('mturk.tasks', $contract->id)}}">{{$contract->title}}</a></td>
            <td>{{strtoupper($contract->metadata->category[0])}}</td>
            <td>
                {{$contract->mturk_created_at}}
               <Br/> By {{$contract->mturk_created_by}}
            </td>
            <td>{{$contract->mturk_sent_at}}
                <Br/>   By {{$contract->mturk_sent_by}}
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
            Showing {{($contracts->currentPage()==1)?"1":($contracts->currentPage()-1)*$contracts->perPage()}} to {{($contracts->currentPage()== $contracts->lastPage())?$contracts->total():($contracts->currentPage())*$contracts->perPage()}} of {{$contracts->total()}} contracts
        </div>
        {!! $contracts->appends($app->request->all())->render() !!}
    </div>
@endif
