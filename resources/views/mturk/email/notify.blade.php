<div class="panel panel-default">
    <div class="panel-heading">{{$contract['title']}}</div>
    <a href="{{route('mturk.tasks', $contract['id'])}}">@lang('mturk.balance_check')</a>

    <div class="panel-body">
        <ul>
            <li>{{ trans('mturk.total_tasks') }}: {{$task['total_pages']}}</li>
            <li>{{ trans('mturk.tasks_completed') }}: {{$task['total_completed']}}</li>
            <li>{{ trans('mturk.tasks_approved') }}: {{$task['total_approved']}}</li>
            <li>{{ trans('mturk.tasks_requiring_action') }}: {{$task['total_pending_approval']}} </li>
        </ul>
        {{ trans('mturk.review_task') }}
    </div>
</div>

