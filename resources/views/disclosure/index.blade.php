@extends('layout.app')
@section('content')
    <div class="panel panel-default" xmlns="http://www.w3.org/1999/html">
        <div class="panel-heading">
            <h3>@lang('contract.disclosure_mode')</h3>
            <p style="font-size: 13px">
                @lang('contract.corporate'): @lang('contract.corporate_explain') </br>
                @lang('contract.government'): @lang('contract.government_explain')
            </p>
        </div>
        <div class="panel-body">
            <table class="table">
                <thead>
                    <th>@lang('contract.country')</th>
                    <th>@lang('contract.government')</th>
                    <th>@lang('contract.corporate')</th>
                    <th>@lang('contract.unknown')</th>

                </thead>
                @foreach($disclosureMode as $code => $mode)
                <tr>
                    <td>{{ _l('codelist/country.'.$code) }} </td>
                    @if($mode['government'] != '0' )
                       <td>
                           <a href=" {{route('contract.index',["country"=>$code,"disclosure"=>"Government"]) }}">{{$mode['government']}} </a>
                            @else <td> 0 </td>
                       </td>
                    @endif

                    @if($mode['company'] != '0')
                       <td><a href="{{route('contract.index',["country"=>$code,"disclosure"=>"Company"])}}">{{$mode['company']}} </a></td>
                        @else <td> 0 </td>
                    @endif
                    @if(($mode['unknown'])!= '0')
                        <td><a href="{{route('contract.index',["country"=>$code,"disclosure"=>"unknown"])}}">{{$mode['unknown']}} </a></td>
                        @else <td> 0 </td>
                    @endif

                </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection