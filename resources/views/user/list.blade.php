@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{ trans('user.user') }} <a class=" btn btn-primary pull-right" href="{{route('user.create')}}">
                {{ trans('user.add_user') }}
            </a>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-responsive">
                <thead>
                <tr>
                    <th>{{ trans('user.id') }}</th>
                    <th>{{ trans('user.name') }}</th>
                    <th>{{ trans('user.role') }}</th>
                    <th>{{ trans('user.organization') }}</th>
                    <th>{{ trans('user.status') }}</th>
                    <th>{{ trans('user.created_on') }}</th>
                    <th width="140px">{{ trans('user.action') }}</th>
                </tr>
                </thead>

                @forelse($users as $key => $user)
                    <tr>
                        <td>{{$user->id}}</td>
                        <td>{{$user->name}}<br/>{{$user->email}}</td>
                        <td>
                            {{$user->roleName()}}<br/>
                            {{!empty($user->country[0]) ? '('.trans('codelist/country.'.$user->country[0]).')':null}}

                        </td>
                        <td>{{$user->organization}}</td>
                        <td>{{$user->status == 'true' ? trans('user.active') : trans('user.inactive')}}</td>
                        <td>{{$user->createdDate('F d Y \a\t h:i A')}}</td>
                        <td>
                            <a href="{{route('user.edit',[ 'id' => $user->id])}}" id="user_edit_{{$key}}" class="btn btn-primary">{{ trans('user.edit') }}</a>

                            {!!Form::open(['route'=>['user.destroy',['id' => $user->id]], 'style'=>"display:inline",
              'method'=>'delete'])!!}
                            {!!Form::button(trans('user.delete'), ['type'=>'submit','id'=>"user_delete_{{$key}}", 'class'=>'btn btn-danger confirm',
                            'data-confirm'=>trans('user.confirm_text_user_delete')])!!}
                            {!!Form::close()!!}

                        </td>

                    </tr>

                    </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang("user.user_not_found")</td>
                    </tr>
                @endforelse


            </table>
        </div>
    </div>
@endsection
