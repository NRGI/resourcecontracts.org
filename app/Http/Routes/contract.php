<?php
$router->group(
    ['namespace' => 'Contract'],
    function () use ($router) {
        $router->resource('contract', 'ContractController');
        $router->get('contract/{id}/pages', ['as' => 'contract.pages', 'uses' => 'Page\PageController@index']);
        $router->get('contract/{id}/page/{page}', ['as' => 'contract.page', 'uses' => 'Page\PageController@getText']);
        $router->post('contract/{id}/page', ['as' => 'contract.page.store', 'uses' => 'Page\PageController@store']);

    }
);