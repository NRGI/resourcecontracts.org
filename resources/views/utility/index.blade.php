@extends('layout.app')
@section('content')


    <div class="panel panel-default" xmlns="http://www.w3.org/1999/html">
        <div class="panel-heading">
                <h3>@lang('contract.utility_autorename')</h3>
            </div>
            <div class="panel-body">
                <div class="col-lg-11">
                    {!! Form::open(['route'=>'utility.index' ,'method' => 'get','class' => 'form-inline']) !!}
                    <div class="form-group">
                        {!! Form::select('category', ['all'=>'Select Category','rc'=>'RC','olc'=>'OLC'], ["class"=>"required form-control"]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::select('country' , ['all'=>'Select Country',trans('codelist/country')], ["class"=>"required form-control"]) !!}
                    </div>

                    {!! Form::button('Submit', ['type' => 'submit','class' => 'btn btn-primary']) !!}
                    {!! Form::close() !!}
                </div>
                <table class="table">
                @if(isset($renameContracts))
                    <thead>
                        <th>Contract Id</th>
                        <th>Contracts Old Name</th>
                        <th>Contracts New Name</th>
                    </thead>
                    <tbody>

                           @foreach($renameContracts as $renameContract)
                                   <tr>
                                       <td>{{ $renameContract['id'] }}</td>
                                       <td>{{ $renameContract['old'] }}</td>
                                       <td>{{ $renameContract['new'] }}</td>
                                   </tr>
                            @endforeach
                            <tr>
                                <td>
                                    {!! Form::open(['route'=>'utility.submit' ,'method' => 'post','class' => 'form-inline' ]) !!}
                                    {!! Form::hidden('con',serialize($renameContracts)) !!}
                                    {!! Form::button('Rename', ['type' => 'submit','class' => 'btn btn-primary confirm','data-confirm'=>'Are you sure you want to rename these contracts?']) !!}
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                    </tbody>
                    @endif
                </table>
            </div>
    </div>
@endsection
