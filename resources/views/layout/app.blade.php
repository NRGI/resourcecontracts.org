@include('layout.partials.header')
<div id="wrapper">
	@if(Auth::user())
	<header class="main-header">
        <nav class="navbar navbar-static-top" role="navigation">
        <div class="navbar-custom-menu">
	        <a href="#menu-toggle" class="btn btn-default pull-left" id="menu-toggle">Menu</a>
            <ul class="nav navbar-nav pull-right">
              <!-- User Account: style can be found in dropdown.less -->
              <li class="dropdown user user-menu">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                  <img src="{{asset('../images/ic_user.png')}}" class="user-image" alt="User Image">
                </a>
                <ul class="dropdown-menu">
                  <li>     
                      {{Auth::user()->email}}
                  </li>
                  <li>     
                      <a href="{{url('/auth/logout')}}">Logout</a>
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </nav>
      </header>
    @endif
    @include('layout.partials.menu')

    <div id="page-content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                   @yield('content')
                </div>
            </div>
        </div>
    </div>
</div>

@include('layout.partials.footer')
