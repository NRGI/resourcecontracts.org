@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$userDetails->name}} <a class=" btn btn-primary pull-right" href="{{url('/profile/edit') }}">
                {{ trans('user.edit_profile') }}</a>
        </div>

        <div class="panel-body">
        <ul class="profile-details">
         <li><strong>{{ trans('user.name') }}:</strong> {{$userDetails->name}}</li>
         <li><strong>{{ trans('user.email') }}:</strong> {{$userDetails->email}}</li>
         <li><strong>{{ trans('user.profile_created_on') }}:</strong> {{$userDetails->created_at->format('Y-m-d')}}
              ({{$userDetails->created_at->diffForHumans()}})
           </li>
           <li><strong>{{ trans('user.organization') }}:</strong> {{$userDetails->organization}}</li>
         <li><strong>{{ trans('user.role') }}:</strong>{{$userDetails->roles[0]->name}}</li>
     </ul>
     </div>

@endsection

