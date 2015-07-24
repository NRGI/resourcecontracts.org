<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Resource Contracts</title>
    <link rel="stylesheet" href="{{asset('css/app.css')}}" />
    <link rel="stylesheet" href="{{asset('css/sidebar.css')}}" />
    <link rel="stylesheet" href="{{asset('css/style.css')}}" />
    <link rel="stylesheet" href="{{asset('css/datepicker.css')}}" />
    <meta name="_token" content="{{ csrf_token() }}"/>
    <script>
		var app_url = '<?php echo url('/');?>';
	</script>
    @yield('css')
</head>
<body>