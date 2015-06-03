@extends('layout.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-heading" style="overflow: hidden">
                        Contracts
                    </div>
                    <div class="panel-body">
                        <table class="table">
                            <a target="_blank" style="float:right" href="{{$contract->file}}"> <i class="glyphicon glyphicon-file"></i> View Document</a>
                            <br class="clear"/>
                            <br class="clear"/>

                            @foreach($contract->metadata as $key => $value)
                                <tr>
                                    <td>{{ ucfirst( str_replace('_', ' ',$key))}}</td>
                                    <td>{{$value}}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td>Created date</td>
                                <td>{{$contract->created_datetime->format('F d, Y')}}</td>
                            </tr>
                            <tr>
                                <td>Last updated date</td>
                                <td>{{$contract->last_updated_datetime->format('F d, Y')}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
