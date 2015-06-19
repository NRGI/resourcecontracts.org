<?php
$router->group(['namespace' => 'Api', 'prefix' => 'api'], function ($router) {
    $router->put('annotations/{id}', 'AnnotationApiController@save');
    $router->post('annotations/{id}', 'AnnotationApiController@delete');
    $router->post('annotations', 'AnnotationApiController@save');
    $router->put('annotations', 'AnnotationApiController@save');
    $router->get('search', 'AnnotationApiController@search');
});
$router->resource('contract.annotations', 'Annotation\AnnotationController');
