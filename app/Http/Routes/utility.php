<?php
$router->group(
    ['namespace' => 'Utility'],
    function ($router) {
        $router->get('utility',['as' => 'utility.index','uses'=>'UtilityController@index']);
        $router->post('utility',['as' => 'utility.submit','uses'=>'UtilityController@save']);
    }
);