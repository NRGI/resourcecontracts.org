<?php

$router->get('/', 'Auth\LoginController@showLoginForm');

Auth::routes(['register'=>false]);

$router->get('home', 'Dashboard\DashboardController@index');

$router->group(
    ['namespace' => 'Api', 'prefix' => 'api'],
    function ($router) {
        $router->post('login',['as' => 'api.login','uses'=>'ApiController@login']);
        $router->get('codelist/{lang}',['as' => 'api.codelist','uses'=>'ApiController@getCodeList']);
    }
);

$router->group(['middleware' => 'auth'],function($router){
    $router->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
});