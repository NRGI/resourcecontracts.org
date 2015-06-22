<?php
$router->group(
    ['namespace' => 'ActivityLog'],
    function ($router) {
        $router->get('activities',['as' => 'activitylog.index','uses'=>'ActivityLogController@index']);
    }
);
