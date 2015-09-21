@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">Users <a class=" btn btn-primary pull-right" href="{{route('user.create')}}">Add
                User</a>
        </div>
        <div class="panel-body">
            <table class="table table-striped table-responsive">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Organization</th>
                        <th>Status</th>
                        <th>Created on</th>
                        <th></th>
                    </tr>
                </thead>

                @forelse($users as $key => $user)
                    <tr>
                        <td>{{$user->id}}</td>
                        <td>{{$user->name}}</td>
                        <td>{{$user->email}}</td>
                        <td>{{$user->organization}}</td>
                        <td>{{$user->status == 'true' ? 'Active' : 'Inactive'}}</td>
                        <td>{{$user->created_at->format('D M d, Y h:i A')}}</td>
                        <td>
                            <a href="{{route('user.edit', $user->id)}}" id="user_edit_{{$key}}" class="btn btn-primary">Edit</a>
                            <a href="{{route('user.delete', $user->id)}}" id="user_delete_{{$key}}" class="btn btn-danger confirm"
                               data-confirm="Are you sure you want to delete this user?">Delete</a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang('User not found.')</td>
                    </tr>
                @endforelse


            </table>
        </div>
    </div>
@endsection
