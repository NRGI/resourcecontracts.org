<?php
$router->group(
    ['namespace' => 'Quality'],
    function ($router) {
        $router->get('quality',['as' => 'quality.index','uses'=>'QualityController@index']);
    }
);