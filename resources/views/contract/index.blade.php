@extends('layout.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading" style="overflow: hidden">
                        Contracts
                        <div class="pull-right"><a href="{{route('contract.create')}}" class="btn btn-primary">Add</a>
                        </div>
                    </div>
                    <div class="panel-body">

                        <table class="table">
                            <tr>
                                <th>Project Title</th>
                                <th>Action</th>
                            </tr>
                            @if($contracts->count() > 0)
                                @foreach($contracts as $contract)
                                    <tr>
                                        <td>{{$contract->metadata->project_title}}</td>
                                        <td>
                                            <a title="Edit Contract" style="float: left; margin-right: 5px;" class="btn btn-default"  href="{{route('contract.show', $contract->id)}}"><i
                                                        class="glyphicon glyphicon-eye-open"></i></a>

                                            <a title="View Contract" style="float: left; margin-right: 5px;" class="btn btn-default"  href="{{route('contract.edit', $contract->id)}}"><i
                                                        class="glyphicon glyphicon-pencil"></i></a>

                                            {!! Form::open(['route' => array('contract.destroy', $contract->id ), 'class'=>'form-inline', 'method'=>'delete']) !!}
                                            <button onclick="if(confirm('Are you sure you want delete contract?')){ return true;} else {return false;}" class="btn btn-default"><i class="glyphicon glyphicon-trash"></i></button>
                                        </td>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="2">Contract not found.</td>
                                </tr>
                            @endif
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
