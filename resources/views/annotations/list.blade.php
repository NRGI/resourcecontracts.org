@extends('layout.app')
@section('content')
    <div class="container ">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Annotaion List </div>
                    <div class="panel-body">
                        <table class="table">
                        <tr>
                            <th>Text</th>
                            <th>Comment</th>
                        </tr>
                            @foreach($annotations as $annotation)
                            <tr>
                                <td>{{$annotation->annotation->text}}</td>
                                <td>{{$annotation->annotation->quote}}</td>

                            </tr>
                            @endforeach
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

