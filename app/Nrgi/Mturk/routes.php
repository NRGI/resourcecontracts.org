<?php
$router->get('mturk/public', 'MTurkController@publicPage');
$router->get('/mturk', ['as' => 'mturk.index', 'uses' => 'MTurkController@index']);
$router->get('/mturk/tasks', ['as' => 'mturk.allTasks', 'uses' => 'MTurkController@allTasks']);
$router->post('/mturk/add/{contract_id}/tasks', ['as' => 'mturk.add', 'uses' => 'MTurkController@createTasks']);
$router->get('/mturk/activity/', ['as' => 'mturk.activity', 'uses' => 'MTurkController@activity']);
$router->get('/mturk/reset/', ['as' => 'mturk.task.reset.cmd', 'uses' => 'MTurkController@resetHitCmd']);
$router->get('/mturk/restore/', ['as' => 'mturk.task.reset.cmd', 'uses' => 'MTurkController@restoreHitCmd']);
$router->get('/mturk/{contract_id}', ['as' => 'mturk.tasks', 'uses' => 'MTurkController@tasksList']);
$router->post('/mturk/{contract_id}/copy', ['as' => 'mturk.contract.copy', 'uses' => 'MTurkController@sendToRC']);
$router->post('/mturk/{contract_id}/all/approve', ['as' => 'mturk.task.approveAll', 'uses' => 'MTurkController@approveAll']);
$router->get('/mturk/{contract_id}/{task_id}', ['as' => 'mturk.task.detail', 'uses' => 'MTurkController@taskDetail']);
$router->post('/mturk/{contract_id}/{task_id}/approve', ['as' => 'mturk.task.approve', 'uses' => 'MTurkController@approve']);
$router->post('/mturk/{contract_id}/{task_id}/reject', ['as' => 'mturk.task.reject', 'uses' => 'MTurkController@reject']);
$router->post('/mturk/{contract_id}/{task_id}/reset', ['as' => 'mturk.task.reset', 'uses' => 'MTurkController@resetHit']);
$router->post('/mturk/{contract_id}/{task_id}/approved/reset', ['as' => 'mturk.task.approved.reset', 'uses' => 'MTurkController@resetApprovedHit']);
