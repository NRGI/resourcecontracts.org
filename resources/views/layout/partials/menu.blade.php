@if(Auth::user())
    <div id="sidebar-wrapper">
        <ul class="sidebar-nav">
            <li class="sidebar-brand">
                <a href="/">
                    Resource Contracts
                </a>
            </li>
            <li>{{Auth::user()->email}}</li>
            <li><a href="{{url('/auth/logout')}}">Logout</a></li>
            <li>
                <a href="{{route('contract.index')}}">All Contracts</a>
            </li>

        </ul>
    </div>
@endif