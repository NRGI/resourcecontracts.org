<?php
$router->group(
    ['namespace' => 'User'],
    function () use ($router) {
        $router->get('user', ['as' => 'user.list', 'uses' => 'UserController@index']);
        $router->get('user/create', ['as' => 'user.create', 'uses' => 'UserController@create']);
        $router->post('user/store', ['as' => 'user.store', 'uses' => 'UserController@store']);
        $router->get('user/{id}/edit', ['as' => 'user.edit', 'uses' => 'UserController@edit']);
        $router->patch('user/{id}/update', ['as' => 'user.update', 'uses' => 'UserController@update']);
        $router->get('user/{id}/delete', ['as' => 'user.delete', 'uses' => 'UserController@delete']);

        $router->get('profile', ['as' => 'user.profile', 'uses' => 'ProfilesController@profile']);
        $router->get('profile/edit', ['as' => 'user.editProfile', 'uses' => 'ProfilesController@editProfile']);

        $router->patch('profile/update', ['as' => 'profile.update', 'uses' => 'ProfilesController@updateUser']);

    }
);

