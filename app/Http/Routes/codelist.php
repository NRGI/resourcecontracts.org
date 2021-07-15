<?php
$router->group(
    ['namespace' => 'CodeList'],
    function () use ($router) {
        $router->get('codelist/{type}', ['as' => 'codelist.list', 'uses' => 'CodeListController@index']);
        $router->get('codelist/create/{type}', ['as' => 'codelist.create', 'uses' => 'CodeListController@create']);
        $router->post('codelist/store', ['as' => 'codelist.store', 'uses' => 'CodeListController@store']);
        $router->get('codelist/{type}/{id}/edit', ['as' => 'codelist.edit', 'uses' => 'CodeListController@edit']);
        $router->patch('codelist/{id}/update', ['as' => 'codelist.update', 'uses' => 'CodeListController@update']);
        $router->delete('codelist/{id}/{type}/delete', ['as' => 'codelist.destroy', 'uses' => 'CodeListController@delete']);
    });