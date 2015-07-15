@extends('layout.app')
@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('Contracts Sent for Mturk')
        <a class="btn btn-primary pull-right" href="{{route('mturk.activity')}}">@lang('mturk.activity')</a>
    </div>
        <div class="panel-body">
            <table class="table table-striped table-responsive">
                <thead>
                <tr>
                    <th width="40%">@lang('mturk.contract_name')</th>
                    <th>@lang('mturk.pages')</th>
                    <th>@lang('mturk.tasks')</th>
                    <th>@lang('mturk.completed')</th>
                    <th>@lang('mturk.approved')</th>
                    <th>@lang('mturk.rejected')</th>
                    <th>@lang('mturk.action_name')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($contracts as $contract)
                    <tr>
                        <td><a href="{{route('mturk.tasks', $contract->id)}}">{{$contract->title}}</a></td>
                        <td>{{$contract->tasks->count()}}</td>
                        <td>{{$contract->total_hits}}</td>
                        <td>{{$contract->count_status['total_completed']}}</td>
                        <td>{{$contract->count_status['total_approved']}}</td>
                        <td>{{$contract->count_status['total_rejected']}}</td>
                        <td>
                            @if($contract->tasks->count() == $contract->count_status['total_approved'])
                                @if($contract->mturk_status == 2)
                                    <button class="btn btn-default" disabled="disabled">Sent to RC</button>
                                @else
                                    {!! Form::open(['url' =>route('mturk.contract.copy',$contract->id), 'method' => 'post']) !!}
                                    {!! Form::button(trans('Send to RC'), ['type' =>'submit', 'class' => 'btn btn-success confirm', 'data-confirm'=>'Are you sure you want to send text to RC?'])!!}
                                    {!! Form::close() !!}
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">@lang('mturk.not_found')</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
