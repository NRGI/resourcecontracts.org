<?php
$router->get('/', 'Auth\AuthController@getLogin');
$router->get('home', 'Dashboard\DashboardController@index');
$router->controllers(
    [
        'auth' => 'Auth\AuthController',
        'password' => 'Auth\PasswordController',
    ]
);

$router->group(
    ['namespace' => 'Api', 'prefix' => 'api'],
    function ($router) {
        $router->post('login',['as' => 'api.login','uses'=>'ApiController@login']);
    }
);

$router->group(['middleware' => 'auth'],function($router){
    $router->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
});