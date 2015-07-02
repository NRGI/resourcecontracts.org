<?php
$router->group(
    ['namespace' => 'Contract'],
    function () use ($router) {
        $router->resource('contract', 'ContractController');
        $router->post('contract/{id}/page', ['as' => 'contract.page.store', 'uses' => 'Page\PageController@store']);
        $router->post('contract/{id}/output', ['as' => 'contract.output.save', 'uses' => 'ContractController@saveOutputType']);
        $router->post('contract/{id}/status', ['as' => 'contract.status', 'uses' => 'ContractController@updateStatus']);
        $router->post('contract/{id}/status/comment', ['as' => 'contract.status.comment', 'uses' => 'ContractController@contractComment']);
        $router->get('contract/{id}/comment', ['as' => 'contract.comment.list', 'uses' => 'Comment\CommentController@index']);
        $router->get('contract/{id}/metadata', ['as' => 'contract.metadata', 'uses' => 'ContractController@getMetadata']);

        $router->get('contract/{id}/pages', ['as' => 'contract.pages', 'uses' => 'Page\PageController@index']);
        $router->get('contract/{id}/page', ['as' => 'contract.page.get', 'uses' => 'Page\PageController@getText']);
        $router->post('contract/{id}/pages/search', ['as' => 'contract.page.search', 'uses' => 'Page\PageController@search']);
        $router->get('contract/{id1}/{id2}/compare', ['as' => 'contract.page.compare', 'uses' => 'Page\PageController@compare']);
    }
);
