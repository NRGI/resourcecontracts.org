<!doctype html>
<html lang="{{$lang->current()->code}}">
<head>
    <meta charset="UTF-8"/>
    <title>@lang('global.contracts')</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}"/>
    <link rel="stylesheet" href="{{asset('css/sidebar.css')}}"/>
    <link rel="stylesheet" href="{{asset('css/style.css')}}"/>
    <link rel="stylesheet" href="{{asset('css/datepicker.css')}}"/>
    <meta name="_token" content="{{ csrf_token() }}"/>
    <script>
        var app_url = '{{url()}}';
    </script>
    @yield('css')
</head>
<body class="lang-{{$lang->current()->code}}" dir="{{$lang->dir()}}">
