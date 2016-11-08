@extends('layout.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">@lang('contract.import.title')</div>
        <div class="panel-body">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>@lang('contract.whoops')</strong> @lang('contract.problem')<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @forelse($jobs as $job)
                <table class="table">
                    <thead>
                        <tr>
                            <th width="30%">@lang('contract.import.created_date')</th>
                            <th>@lang('contract.import.file_name')</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tr>
                        @if($job['step'] == 1)
                         <td>{{date( 'd F, Y H:i A', $job['file']['created_at'])}}</td>
                         <td><a href="{{route('contract.import.confirm', $job['key'])}}">{{$job['file']['name']}}</a></td>
                        @endif

                        @if($job['step'] == 2)
                           <td>{{date( 'Y-m-d H:i:s', $job['file']['created_at'])}}</td>
                           <td><a href="{{route('contract.import.status', $job['key'])}}">{{$job['file']['name']}}</a></td>
                            <td></td>
                        @endif
                    </tr>
                </table>

                @if($job['is_completed'])
                    <div style="margin-top: 20px;">
                        {!!Form::open(['route'=>['contract.import.delete', $job['key']], 'style'=>"display:inline",
                        'method'=>'delete'])!!}
                        {!!Form::button(trans('contract.import.upload_another'), ['type'=>'submit','class'=>'btn btn-primary'])!!}
                        {!!Form::close()!!}
                    </div>
                @endif
            @empty
            {!! Form::open(['route' => 'contract.import.post', 'method' => 'post', 'files'=>true]) !!}
            <div class="form-group">
                <label for="Select PDF" class="col-sm-2 control-label">@lang('contract.import.file') <span
                            class="red">*</span></label>
                <div class="col-sm-7">
                    {!! Form::file('file', ['class'=>'required'])!!}
                    <p class="help-block">@lang('contract.import.help', ['format' => sprintf('<a target="_blank" href="%s">format</a>', url('/sample.csv') )]).</p>
                </div>
            </div>
            <div class="form-action">
                <div class="col-sm-7 col-lg-offset-2">
                    {!! Form::submit(trans('contract.submit'),['class'=>'btn btn-lg btn-primary']) !!}
                </div>
            </div>
            {!! Form::close() !!}
            @endforelse
        </div>
    </div>
@stop