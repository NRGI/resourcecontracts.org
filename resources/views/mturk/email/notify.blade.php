<div class="panel panel-default">
    <div class="panel-heading">{{$contract['title']}}</div>
    <a href="{{route('mturk.tasks', $contract['id'])}}">click here to check</a>

    <div class="panel-body">
        <ul>
            <li>Total tasks: {{$task['total_pages']}}</li>
            <li>Tasks completed: {{$task['total_completed']}}</li>
            <li>Tasks approved: {{$task['total_approved']}}</li>
            <li>Tasks requiring your action: {{$task['total_pending_approval']}} </li>
        </ul>
        Please review the completed tasks before approval.
    </div>
</div>

