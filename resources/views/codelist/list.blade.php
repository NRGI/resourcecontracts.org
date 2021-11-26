@extends('layout.app')

@section('content')
<div class="code-list-panel-heading">
    <ul class="nav nav-tabs code-list-panel-heading__items">
        <li {{in_array('contract_types', Request::segments())? 'class=active' : ''}}>
            <a href="{{route('codelist.list', 'contract_types')}}">
                {{ trans('codelist.contract_types') }}
            </a>
        </li>
        <li {{in_array('document_types', Request::segments())? 'class=active' : ''}}>
            <a href="{{route('codelist.list', 'document_types')}}">
                {{ trans('codelist.document_types') }}
            </a>
        </li>
        <li {{in_array('resources', Request::segments())? 'class=active' : ''}}>
            <a href="{{route('codelist.list', 'resources')}}">
                {{ trans('codelist.resources') }}
            </a>
        </li>
    </ul>
</div>
    <div class="panel panel-default code-list-panel">
        <div class="panel-body">
            <div class="clearfix">
                <a class=" btn btn-primary pull-right" href="{{route('codelist.create', $type)}}">
                    {{ trans('codelist.add_'.$type) }}
                </a>
            </div>

            <table class="table table-striped table-responsive code-list-table">
                <thead>
                <tr>
                    <th>{{ trans('codelist.english') }}</th>
                    <th>{{ trans('codelist.french') }}</th>
                    <th>{{ trans('codelist.arabic') }}</th>
                    <th width="160px">{{ trans('codelist.action') }}</th>
                </tr>
                </thead>

                @forelse($data as $key => $value)
                    <tr>
                        <td>{{$value->en}}</td>
                        
                        <td>{{$value->fr}}</td>
                        <td>{{$value->ar}}</td>
                        <td>
                            <a href="{{route('codelist.edit', [$type,$value->id])}}" id="codelist_edit_{{$key}}" class="btn btn-primary">{{ trans('codelist.edit') }}</a>

                            {!!Form::open(['route'=>['codelist.destroy',$type, $value->id], 'style'=>"display:inline",'method'=>'delete'])!!}
                            {!!Form::button(trans('codelist.delete'), ['type'=>'submit','id'=>"codelist_delete_{{$key}}", 'class'=>'btn btn-danger confirm',
                            'data-confirm'=>trans('codelist.confirm_text_'.$type.'_delete')])!!}
                            {!!Form::close()!!}

                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="7">@lang("codelist.".$type."_not_found")</td>
                    </tr>
                @endforelse
            </table>
            {!!$data->appends(Request::all())->render()!!}
        </div>
    </div>
@endsection
