<?php
$router->group
    ( ['namespace'=>'Disclosure'],
    function($router) {
        $router->get('disclosure',['as' => 'disclosure.index','uses' => 'DisclosureController@index']);
    }

);