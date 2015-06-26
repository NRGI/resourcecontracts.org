@if($current_user)
    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            <li class="sidebar-brand">
                <a href="/">
                    @lang('contract.resource_contracts')
                </a>
            </li>
            <li>
                <a href="{{route('contract.index')}}">@lang('contract.all_contract')</a>
            </li>

            @if($current_user->hasRole('superadmin'))
                <li>
                    <a href="{{route('user.list')}}">@lang('contract.users')</a>
                </li>
            @endif

        </ul>
    </div>
@endif