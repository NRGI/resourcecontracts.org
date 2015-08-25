<?php

$router->get('/', 'Auth\AuthController@getLogin');
$router->get('home', 'Dashboard\DashboardController@index');
$router->controllers(
    [
        'auth' => 'Auth\AuthController',
        'password' => 'Auth\PasswordController',
    ]
);

$router->get('/site/login', 'Auth\AuthController@siteLogin');

$router->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');