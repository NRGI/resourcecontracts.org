<?php
$router->group(
    ['namespace' => 'User'],
    function () use ($router) {
        $router->get('user', ['as' => 'user.list', 'uses' => 'UserController@index']);
        $router->get('user/create', ['as' => 'user.create', 'uses' => 'UserController@create']);
        $router->post('user/store', ['as' => 'user.store', 'uses' => 'UserController@store']);
        $router->get('user/{id}/edit', ['as' => 'user.edit', 'uses' => 'UserController@edit']);
        $router->patch('user/{id}/update', ['as' => 'user.update', 'uses' => 'UserController@update']);
        $router->delete('user/{id}/delete', ['as' => 'user.destroy', 'uses' => 'UserController@delete']);
        $router->post('user/{id}/deactivate', ['as' => 'user.deactivate', 'uses' => 'UserController@deactivate']);
        $router->get('profile', ['as' => 'user.profile', 'uses' => 'ProfilesController@profile']);
        $router->get('profile/edit', ['as' => 'user.editProfile', 'uses' => 'ProfilesController@editProfile']);
        $router->patch('profile/update', ['as' => 'profile.update', 'uses' => 'ProfilesController@updateUser']);

        $router->get('user/role', ['as' => 'role', 'uses' => 'RoleController@index']);
        $router->post('user/role', ['as' => 'role.store', 'uses' => 'RoleController@store']);
        $router->patch('user/role', ['as' => 'role.update', 'uses' => 'RoleController@update']);
        $router->delete('user/role/{id}/delete', ['as' => 'role.destroy', 'uses' => 'RoleController@delete']);
    }
);
