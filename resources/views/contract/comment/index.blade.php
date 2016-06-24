@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang("All Comments") - {{$contract->metadata->contract_name}} <a class="btn btn-default pull-right" href="{{route('contract.show', $contract->id)}}">@lang("global.back")</a></div>
        <div class="panel-body">
            <div class="" id="myTabs">
                <ul class="nav nav-tabs">
                    <li class="active"><a id="tabAll">@lang('mturk.all')</a></li>
                    <li><a href="#" id="tabMetadata">@lang('global.metadata')</a></li>
                    <li><a href="#" id="tabText">@lang('global.text')</a></li>
                    <li><a href="#" id="tabAnnotation">@lang('contract.annotations')</a></li>
                </ul>
            </div>

            <div class="tab-content">
            @forelse($comments as $comment)
                <div class="comment-section tab-pane-{{$comment->type}} active" id="{{$comment->type}}">
                    <div class="comment">
                       {{$comment->message}}
                            <div class="label label-default label-comment">{{ucfirst($comment->type)}}</div>
                       </div>
                        <div class="comment-info">
                            <span class="{{$comment->action}}">{{ucfirst($comment->action)}}</span>
                            @lang('mturk.by') <strong>{{$comment->user->name}}</strong>
                            @lang('mturk.on') {{$comment->created_at->format('D F d, Y h:i a')}}
                        </div>
                    </div>
                @empty
                    <div class="no-comment">
                        {{trans('contract.comment_not_added')}}
                    </div>
            @endforelse
            </div>
            </div>
            {!!$comments->render()!!}
    </div>
@stop
