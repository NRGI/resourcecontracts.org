<?php
$router->group(
    ['namespace' => 'Contract'],
    function () use ($router) {
        $router->resource('contract', 'ContractController');
        $router->get('contract/{id}/edit/{lang}', ['as' => 'contract.edit.trans', 'uses' => 'ContractController@edit']);
        $router->get('contract/discussion/{id}/{type}/{key}', ['as' => 'contract.discussion', 'uses' => 'Discussion\DiscussionController@index']);
        $router->post('contract/discussion/{id}/{type}/{key}', ['as' => 'contract.discussion.create', 'uses' => 'Discussion\DiscussionController@create']);
        $router->get('contract/{id}/download', ['as' => 'contract.download', 'uses' => 'ContractController@download']);
        $router->post('contract/{id}/page', ['as' => 'contract.page.store', 'uses' => 'Page\PageController@store']);
        $router->post('contract/{id}/output', ['as' => 'contract.output.save', 'uses' => 'ContractController@saveOutputType']);
        $router->post('contract/{id}/status', ['as' => 'contract.status', 'uses' => 'ContractController@updateStatus']);
        $router->post('contract/{id}/status/comment', ['as' => 'contract.status.comment', 'uses' => 'ContractController@contractComment']);
        $router->post('contract/{id}/publish', ['as' => 'contract.publish', 'uses' => 'ContractController@publish']);
        $router->get('contract/{id}/comment', ['as' => 'contract.comment.list', 'uses' => 'Comment\CommentController@index']);
        $router->get('contract/{id}/metadata', ['as' => 'contract.metadata', 'uses' => 'ContractController@getMetadata']);
        $router->get('contract/{id}/oldpages', ['as' => 'contract.oldpages', 'uses' => 'Page\PageController@index']);
        $router->get('contract/{id}/page', ['as' => 'contract.page.get', 'uses' => 'Page\PageController@getText']);
        $router->post('contract/{id}/pages/search', ['as' => 'contract.page.search', 'uses' => 'Page\PageController@search']);
        $router->get('contract/{id1}/{id2}/compare', ['as' => 'contract.page.compare', 'uses' => 'Page\PageController@compare']);
        $router->get('contract/{id}/allpage', ['as' => 'contract.allpage.get', 'uses' => 'Page\PageController@getAllText']);
        $router->post('contract/{id}/unpublish', ['as' => 'contract.unpublish', 'uses' => 'ContractController@unPublish']);
        $router->get('contract/{id}/annotate', ['as' => 'contract.annotate', 'uses' => 'Page\PageController@annotatenew']);
        $router->get('contract/{id}/review', ['as' => 'contract.review', 'uses' => 'Page\PageController@reviewnew']);
        $router->get('contract/select/type', ['as' => 'contract.select.type', 'uses' => 'ContractController@contractType']);
        $router->any('contract/generate/name',['as' => 'contract.generate.name', 'uses' =>'ContractController@getContractName']);
        $router->get('contract/{id}/{lang}', ['as' => 'contract.show.trans', 'uses' => 'ContractController@show']);
    }
);
