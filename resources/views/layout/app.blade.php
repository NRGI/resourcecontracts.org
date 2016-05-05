@include('layout.partials.header')
<div id="wrapper">
    @if($current_user)
        <header class="main-header">
            <nav class="navbar navbar-static-top" role="navigation">
                <div class="navbar-custom-menu">
                    {{--<a href="#menu-toggle" class="btn btn-default pull-left" id="menu-toggle">@lang('global.menu')</a>--}}
                    <div class="btn-group">
                    <?php
                        $getLanguge = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
                        $supportedLanguage = array('en', 'fr');
                        $language = preg_split('/[,;]/', $getLanguge);
                            foreach($language as $lang){
                                if(in_array($lang,$supportedLanguage)){
                                    if(!empty($lang) && ($lang!='en')){
                                            echo('<div class = "alert alert-info alert-dismissable translateBox">
                                                    <button type = "button" class = "close" data-dismiss = "alert" aria-hidden = "true">&times;</button>
                                                    Do you want to translate the site to '.$lang.'
                                                    <button type = "Submit" class = "btn btn-default translate" data-lang ='.$lang.'>Translate</button>
                                                    </div>');
                                    }
                                }
                            }

                        ?>
                        {{--<button type="button" class="btn btn-default">{{app()->getLocale()}}</button>--}}
                        {{--<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
                            {{--<span class="caret"></span>--}}
                            {{--<span class="sr-only">Toggle Dropdown</span>--}}
                        {{--</button>--}}
                        {{--<ul class="dropdown-menu">--}}
                            {{--<li><a href="{{url('/contract',['lang'=>'EN'])}}">EN</a></li>--}}
                            {{--<li><a href="{{url(\Request::url(),['lang'=>'FR'])}}">FR</a></li>--}}
                        {{--</ul>--}}
                    </div>

                    <div class="navbar-custom-menu">
                    <a href="#menu-toggle" class="btn btn-default pull-left" id="menu-toggle">@lang('global.menu')</a>


                    <form action="" method="get">
                        <div class="form-group">
                            <select style="width: 150px;" onchange="this.form.submit();" class="form-control translate">
                                <option @if(app()->getLocale() == 'en') selected="selected" @endif value="en">English</option>
                                <option @if(app()->getLocale() == 'es') selected="selected" @endif value="es">Spanish</option>
                            </select>
                        </div>
                    </form>


                    <ul class="nav navbar-nav pull-right">
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
                                    <a href="{{url('/auth/logout')}}">@lang('global.logout')</a>
                                </li>

                            </ul>
                        </li>
                    </ul>
                </div>
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



