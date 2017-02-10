<?php
$router->group(
    ['namespace' => 'Annotation\Api', 'prefix' => 'api'],
    function ($router) {
        $router->get(
            'annotations/{contractId}',
            [
                'as'   => 'contract.annotations',
                'uses' => 'ApiController@getContractAnnotations'
            ]
        );
        $router->put('annotations/{id}', ['as' => 'annotation.save', 'uses' => 'ApiController@save']);
        $router->put('annotations', ['as' => 'annotation.save.put', 'uses' => 'ApiController@save']);
        $router->post('annotations', ['as' => 'annotation.save.post', 'uses' => 'ApiController@save']);
        $router->get('search', 'ApiController@search');
        $router->post(
            'annotations/{id}',
            [
                'as'   => 'annotation.delete',
                'uses' => 'ApiController@delete'
            ]
        );
        $router->post(
            '/annotation/update',
            [
                'as'   => 'annotation.update',
                'uses' => 'ApiController@update'
            ]
        );
        $router->post(
            'annotation/{id}/delete',
            [
                'as'   => 'annotation.delete',
                'uses' => 'ApiController@delete'
            ]
        );
    }
);
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
