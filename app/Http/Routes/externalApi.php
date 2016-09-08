<?php
$router->delete(
    'external-api/remove',
    [
        'as'   => 'external-api.remove',
        'uses' => 'ExternalApi\ExternalApiController@remove',
    ]
);
$router->post(
    'external-api/update',
    [
        'as'   => 'external-api.update',
        'uses' => 'ExternalApi\ExternalApiController@update',
    ]
);
$router->post(
    'external-api/indexAll',
    [
        'as'   => 'external-api.indexAll',
        'uses' => 'ExternalApi\ExternalApiController@indexAll',
    ]
);

$router->resource('external-api', 'ExternalApi\ExternalApiController', ['except' => ['show', 'update', 'edit']]);
