@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-body">
           <h4>@lang('user.deactivate_message', ['user'=> sprintf('<b>(%s)</b>',$user->name)]) @if($user->status == 'true') @lang('user.can_deactivate') @endif</h4>
            <div style="margin-top: 30px;">
                <a class="btn btn-primary" href="{{route('user.list')}}">{{ trans('user.back') }}</a>
                @if($user->status == 'true')
                    {!!Form::open(['route'=>['user.deactivate', $user->id], 'style'=>"display:inline",
                     'method'=>'post'])!!}
                    {!!Form::button(trans('contract.disable'), ['type'=>'submit','id'=>"user_disable", 'class'=>'btn btn-danger'])!!}
                    {!!Form::close()!!}
                @endif
            </div>
        </div>
    </div>
@endsection

