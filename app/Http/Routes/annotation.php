<?php
$router->get('annotation/demo', 'ContractAnnotationController@index');
$router->group(['namespace' => 'Api', 'prefix' => 'api'], function($router){
    $router->put('annotations', 'AnnotationApiController@save');
    $router->delete('annotations', 'AnnotationApiController@delete');
    $router->post('annotations','AnnotationApiController@save');
    $router->put('annotations/{id}', 'AnnotationApiController@save');
    $router->get('search', 'AnnotationApiController@search');
});
$router->resource('contract.annotations', 'Annotation\AnnotationController');