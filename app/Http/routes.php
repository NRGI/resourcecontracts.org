<?php
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
use App\Nrgi\Mail\MailQueue;

$router->get('/', 'Auth\AuthController@getLogin');
$router->get('home', 'Dashboard\DashboardController@index');
$router->controllers(
    [
        'auth' => 'Auth\AuthController',
        'password' => 'Auth\PasswordController',
    ]
);

if (env('APP_ENV') == 'local') {
    $router->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
}
