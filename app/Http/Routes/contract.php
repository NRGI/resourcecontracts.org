<?php
$router->group(
    ['namespace' => 'Contract'],
    function () use ($router) {
        $router->resource('contract', 'ContractController');
    }
);