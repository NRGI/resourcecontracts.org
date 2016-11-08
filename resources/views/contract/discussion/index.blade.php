<div class="discussion-wrapper">
    <div class="discussion-body">
        <div class="comment-list">
            @forelse($discussions as $discussion)
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p class="comment-user"><i class="fa fa-user"></i> {{$discussion->user->name}}</p>
                        @if($discussion->status == 1) <span class="label label-success pull-right">@lang('contract.resolved')</span> @endif
                        <p class="comment-time"><i class="fa fa-clock-o"></i> {{$discussion->createdDate('F m, d  \a\t h:i A')}}</p>
                    </div>
                    <div class="panel-body"> {!!nl2br($discussion->message)!!}</div>
                </div>
            @empty
                <div class="panel panel-default">
                    <div class="panel-body">@lang('contract.comment_not_added')</div>
                </div>
            @endforelse
        </div>
    </div>
    <div class="discussion-footer">
        <textarea placeholder="{{ trans('contract.write_comment') }}" class="commentField" style="width: 100%; height: 70px;"></textarea>
        <label>
            <input class="status" name="status" type="checkbox" value="1" @if(isset($discussions[0]) && $discussions[0]->status == '1') checked="checked" @endif />
            @lang('contract.mark_resolved')
        </label>
        <br/>
        <button type="button" data-url="{{route('contract.discussion.create', ['id'=>$contract->id, 'type'=>$type, 'key'=>$key])}}" class="btn btn-comment-submit btn-primary">@lang('global.save')</button>
        <button type="button" class="btn btn-close btn-danger">@lang('contract.close')</button>
    </div>
</div>

<script>
    var LANG = {!! json_encode(trans('contract')) !!};
    LANG.loading = '@lang('annotation.loading')';
</script>

