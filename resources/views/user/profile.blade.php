@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">{{$userDetails->name}} <a class=" btn btn-primary pull-right" href="{{url('/profile/edit') }}">Edit Profile</a>
        </div>

        <div class="panel-body">
        <ul class="profile-details">
         <li><strong>Name:</strong> {{$userDetails->name}}</li>
         <li><strong>Email:</strong> {{$userDetails->email}}</li>
         <li><strong>Profile Created on:</strong> {{$userDetails->created_at->format('Y-m-d')}}
              ({{$userDetails->created_at->diffForHumans()}})
           </li>
           <li><strong>Organization:</strong> {{$userDetails->organization}}</li>
         <li><strong>Role:</strong>{{$userDetails->roles[0]->name}}</li>
     </ul>
     </div>

@endsection

