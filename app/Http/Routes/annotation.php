<?php
$router->group(
    ['namespace' => 'Api', 'prefix' => 'api'],
    function ($router) {
        $router->put('annotations/{id}', 'AnnotationApiController@save');
        $router->post('annotations/{id}', 'AnnotationApiController@delete');
        $router->post('annotations', 'AnnotationApiController@save');
        $router->put('annotations', 'AnnotationApiController@save');
        $router->get('search', 'AnnotationApiController@search');
    }
);
$router->resource('contract.annotations', 'Annotation\AnnotationController');
$router->get(
    '/contract/{id}/annotations/list',
    [
        'as'   => 'contract.annotations.list',
        'uses' => 'Annotation\AnnotationController@show'
    ]
);
$router->post(
    '/contract/{id}/annotations/status',
    [
        'as'   => 'contract.annotations.status',
        'uses' => 'Annotation\AnnotationController@updateStatus'
    ]
);
$router->post(
    '/contract/{id}/annotations/comment',
    [
        'as'   => 'contract.annotations.comment',
        'uses' => 'Annotation\AnnotationController@comment'
    ]
);
