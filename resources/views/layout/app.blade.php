@include('layout.partials.header')
<div id="wrapper">
	@if($current_user)
		<header class="main-header">
			<nav class="navbar navbar-static-top" role="navigation">
				<div class="navbar-custom-menu">
					<a href="#menu-toggle" class="btn btn-default pull-left" id="menu-toggle">@lang('global.menu')</a>
					<ul class="nav navbar-nav pull-right">
						<li class="dropdown user user-menu">
							<a style="padding: 22px 34px 24px 17px" href="#" class="dropdown-toggle" data-toggle="dropdown"
							   aria-expanded="false">
								{{$lang->current()->name}}
							</a>
							<ul class="dropdown-menu">
								<li style="display:none"></li>
								@foreach($lang->getAvailableLang() as $l)
									<?php if($lang->current()->code != $l['code']):?>
									<li>
										<a href="{{lang_url($l['code'])}}">{{$l['name']}}</a>
									</li>
									<?php endif;?>
								@endforeach

							</ul>
						</li>
						<li class="dropdown user user-menu">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<img src="{{asset('images/ic_user.png')}}" class="user-image" alt="User Image">
							</a>
							<ul class="dropdown-menu">
								<li>
									{{$current_user->email}}
									<span class="role-name">{{$current_user->roleName()}}</span>
								</li>
								<li>
									<a href="{{url('/profile')}}">@lang('global.profile')</a>
								</li>
								<li>
									<a href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">@lang('global.logout')</a>
									{{Form::open(['url'=> route('logout'), 'method'=>'post','id'=>'logout-form'])}}
									@csrf
									{{Form::close()}}
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

			@include('layout.partials.notification')

			<div class="row">
				<div class="col-lg-12">
					@yield('content')
				</div>
			</div>
		</div>
	</div>
</div>
@include('layout.partials.footer')



