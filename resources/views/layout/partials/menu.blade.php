@if(Auth::user())
    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            <li class="sidebar-brand">
                <a href="/">
                    @lang('contract.resource_contracts')
                </a>
            </li>
            <li>{{Auth::user()->email}}</li>
            <li><a href="{{url('/auth/logout')}}">@lang('contract.logout')</a></li>
            <li>
                <a href="{{route('contract.index')}}">@lang('contract.all_contract')</a>
            </li>

        </ul>
    </div>
@endif