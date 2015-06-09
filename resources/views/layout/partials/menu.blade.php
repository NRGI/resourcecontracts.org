@if(Auth::user())
    <div class="sidebar">

        <div class="sidebar-heading">
            <div class="page-title">Resource <br/>Contracts</div>
            <div class="user-wrapper">
                <a href="#"><img src="{{asset('images/ic_user.png')}}" alt=""/></a>
                <ul>
                    <li>{{Auth::user()->email}}</li>
                    <li><a href="{{url('/auth/logout')}}">Logout</a></li>
                </ul>
            </div>
        </div>

        <div class="filter-wrapper">
            <ul>
                <li class="active"><a href="{{route('contract.index')}}">All</a></li>
                <li><a href="#">Land Contracts</a></li>
                <li class="dropdown"><a href="#">Resource Contracts</a>
                    <ul>
                        <li><a href="#">Resource1</a></li>
                        <li><a href="#">Resource2</a></li>
                        <li><a href="#">Resource3</a></li>
                        <li><a href="#">Resource4</a></li>
                        <li><a href="#">Resource5</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class=" list-wrap countries">
            <div class="title">Countries</div>
            <ul>
                <li><a href="#">Afghanistan</a></li>
                <li><a href="#">Albania</a></li>
                <li><a href="#">Algeria</a></li>
                <li><a href="#">Andorra</a></li>
                <li><a href="#">Angola</a></li>
            </ul>
            <div class="load-more">
                <a href="#" class="load-more">See all countries</a>
            </div>
        </div>
        <div class="list-wrap recent-contracts">
            <div class="title">Recent Contracts</div>
            <ul>
                <li><a href="#">PetroChad (Mangara) Limited..</a></li>
                <li><a href="#">President Energy, Pirity</a></li>
                <li><a href="#">PetroChad (Mangara) Limited..</a></li>
                <li><a href="#">President Energy, Pirity</a></li>
                <li><a href="#">PetroChad (Mangara) Limited..</a></li>
                <li><a href="#">President Energy, Pirity</a></li>
            </ul>
            <div class="load-more">
                <a href="#" class="load-more">See all Contracts</a>
            </div>
        </div>
    </div>
@endif
