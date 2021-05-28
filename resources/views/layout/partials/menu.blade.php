@if($current_user)
    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            <li class="sidebar-brand">
                <a href="/">
                    @lang('contract.resource_contracts')
                </a>
            </li>
            <li>
                <a {{in_array('contract', Request::segments())? 'class=active' : ''}}
                        href="{{route('contract.index')}}">@lang('contract.all_contract')</a>
            </li>

            @if($current_user->hasRole(['superadmin','admin','country-admin','superadmin-editor']))
                <li>
                    <a {{in_array('user', Request::segments())? 'class=active' : ''}}
                            href="{{route('user.list')}}">@lang('contract.users')</a>
                </li>
            @endif
            <li>
                <a {{in_array('activities', Request::segments())? 'class=active' : ''}}
                        href="{{route('activitylog.index')}}">@lang('activitylog.activitylog')</a>
            </li>
            <li>
                <a {{in_array('mturk', Request::segments())? 'class=active' : ''}}
                        href="{{route('mturk.index')}}">@lang('mturk.mturk')</a>
            </li>
            <li>
                <a {{in_array('quality', Request::segments())? 'class=active' : ''}}
                        href="{{route('quality.index')}}">@lang('quality.quality_contract_issues')</a>
            </li>
            @if($current_user->hasRole(['superadmin','admin','country-admin']))
            <li>
                <a {{in_array('utility', Request::segments())? 'class=active' : ''}}
                   href="{{route('utility.index')}}">@lang('contract.utility')</a>
            </li>
            @endif
            <li>
                <a {{in_array('disclosure', Request::segments())? 'class=active' : ''}}
                   href="{{route('disclosure.index')}}">@lang('contract.disclosure_mode')</a>
            </li>      
            @if($current_user->hasRole(['superadmin-editor']))
            <li>
                <a {{in_array('codelist', Request::segments())? 'class=active' : ''}}
                   href="{{route('codelist.list','contract_types')}}">@lang('codelist.code_list')</a>
            </li>
            @endif
        </ul>
    </div>
@endif