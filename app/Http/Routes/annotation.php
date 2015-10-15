<?php
$router->group(
    ['namespace' => 'Api', 'prefix' => 'api'],
    function ($router) {
        $router->get(
            'annotations/{contractId}',
            [
                'as'   => 'contract.annotations',
                'uses' => 'AnnotationApiController@getContractAnnotations'
            ]
        );
        $router->put('annotations/{id}', 'AnnotationApiController@save');
        $router->post('annotations', 'AnnotationApiController@save');
        $router->put('annotations', 'AnnotationApiController@save');
        $router->get('search', 'AnnotationApiController@search');
        $router->post(
            'annotations/{id}',
            [
                'as'   => 'annotation.delete',
                'uses' => 'AnnotationApiController@delete'
            ]
        );
        $router->post(
            '/annotation/update',
            [
                'as'   => 'annotation.update',
                'uses' => 'AnnotationApiController@update'
            ]
        );
        $router->post(
            'annotation/{id}/delete',
            [
                'as'   => 'annotation.delete',
                'uses' => 'AnnotationApiController@delete'
            ]
        );
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
