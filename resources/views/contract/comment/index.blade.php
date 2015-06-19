@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$contract->metadata->contract_name}}</div>
        <div class="panel-body">
            <table class="table table-responsive">
                @forelse($comments as $comment)
                    <tr>
                        <td>
                            {{$comment->message}}
                            <div class="label label-default label-comment">{{ucfirst($comment->type)}}</div>
                        </td>
                        <td align="right">
                            @lang('by') <strong>{{$comment->user->name}}</strong>
                            @lang('on') {{$comment->created_at->format('D F d, Y h:i a')}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="">{{trans('There is no comment.')}}</td>
                    </tr>
                @endforelse
            </table>

            {!!$comments->render()!!}

        </div>
    </div>
@stop
