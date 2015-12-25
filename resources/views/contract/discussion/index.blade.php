<style>
    div.error {
        text-align: left;
        color: #AC0202;
        font-size: 13px;
    }

    .comment-list {
        max-height: 320px;
        overflow-y: auto;
    }

    .comment-list .panel-default {
        margin-right: 10px;
        margin-bottom: 10px;
    }

    .comment-list .panel-default p {
        font-size: 12px;
    }

    .comment-list .panel-default .label {
        font-size: 11px;
        color: #fff !important;
    }

    .comment-list .panel-default p.comment-user {
        font-size: 13px;
        font-weight: bold;
    }

    .comment-list .panel-default .panel-heading {
        padding: 10px 10px 0px;
    }

    .discussion-wrapper {
        background: #FBFBFB;
        padding: 10px;
        margin-top: 15px;
        border: 1px solid #D4D4D4;
    }

</style>
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
        {!! Form::open(['url' => route('contract.discussion.create', ['id'=>$contract->id, 'type'=>$type, 'key'=>$key]), 'method' => 'post', 'id'=>'commentForm']) !!}
        <textarea placeholder="Write comment..." id="commentField" name="comment" style="width: 100%; height: 70px;"></textarea>
        <label>
            {!! Form::checkbox('status', '1', (isset($discussions[0]) && $discussions[0]->status == '1'), ['id' => 'name']) !!}
            Mark as resolved
        </label>
        <br/>
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-default btn-close">Close</button>
        {!! Form::close() !!}
    </div>
</div>
