<div class="discussion-wrapper">
    <div class="discussion-body">
        <div class="comment-list">
            @forelse($discussions as $discussion)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p class="comment-user"><i class="fa fa-user"></i> {{$discussion->user->name}}</p>
                        @if($discussion->status == 1) <span class="label label-success pull-right">Resolved</span> @endif
                        <p class="comment-time"><i class="fa fa-clock-o"></i> {{$discussion->created_at}}</p>
                    </div>
                    <div class="panel-body"> {!!nl2br($discussion->message)!!}</div>
                </div>
            @empty
                <div class="panel panel-default">
                    <div class="panel-body">Comment not added yet.</div>
                </div>
            @endforelse
        </div>
    </div>
    <div class="discussion-footer">
        <textarea placeholder="Write comment..." class="commentField" style="width: 100%; height: 70px;"></textarea>
        <label>
            <input class="status" name="status" type="checkbox" value="1" @if(isset($discussions[0]) && $discussions[0]->status == '1') checked="checked" @endif />
            Mark as resolved
        </label>
        <br/>
        <button type="button" data-url="{{route('contract.discussion.create', ['id'=>$contract->id, 'type'=>$type, 'key'=>$key])}}" class="btn btn-comment-submit btn-primary">Save</button>
        <button type="button" class="btn btn-default btn-close">Close</button>
    </div>
</div>

