<?php
$router->group(
    ['namespace' => 'Contract'],
    function () use ($router) {
        $router->get('contract/import', ['as' => 'contract.import', 'uses' => 'Import\ImportController@index']);
        $router->post('contract/import', ['as' => 'contract.import.post', 'uses' => 'Import\ImportController@importPost']);
        $router->get('contract/import/confirm/{key}', ['as' => 'contract.import.confirm', 'uses' => 'Import\ImportController@Confirm']);
        $router->post('contract/import/confirm/{key}', ['as' => 'contract.import.confirm.post', 'uses' => 'Import\ImportController@confirmPost']);
        $router->get('contract/import/status/{key}', ['as' => 'contract.import.status', 'uses' => 'Import\ImportController@status']);
        $router->delete('contract/import/delete/{key}', ['as' => 'contract.import.delete', 'uses' => 'Import\ImportController@delete']);
        $router->get('contract/import/notify/{key}', ['as' => 'contract.import.notify', 'uses' => 'Import\ImportController@notify']);
    }
);
