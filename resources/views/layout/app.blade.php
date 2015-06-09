@include('layout.partials.header')
<div class="wrapper">
    @include('layout.partials.menu')

    @yield('content')
</div>

@include('layout.partials.footer')
