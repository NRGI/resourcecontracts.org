<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::get('/', 'App\Http\Controllers\Auth\LoginController@showLoginForm');
Route::get('home', 'App\Http\Controllers\Dashboard\DashboardController@index')->middleware('auth');
Auth::routes();
Route::get('logout', 'App\Http\Controllers\Auth\LoginController@logout');

Route::middleware('auth')->get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

/*
|--------------------------------------------------------------------------
| Internal API
|--------------------------------------------------------------------------
*/
Route::prefix('api')->group(function () {
    Route::post('login',  ['as' => 'api.login',    'uses' => 'App\Http\Controllers\Api\ApiController@login']);
    Route::get('codelist/{lang}', ['as' => 'api.codelist', 'uses' => 'App\Http\Controllers\Api\ApiController@getCodeList']);
});

/*
|--------------------------------------------------------------------------
| Contracts
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::prefix('contract')->group(function () {
        // Resource
        Route::resource('contract', 'App\Http\Controllers\Contract\ContractController');
        Route::get('{id}/edit/{lang}',    ['as' => 'contract.edit.trans',    'uses' => 'App\Http\Controllers\Contract\ContractController@edit']);
        Route::get('{id}/download',       ['as' => 'contract.download',      'uses' => 'App\Http\Controllers\Contract\ContractController@download']);
        Route::post('{id}/page',          ['as' => 'contract.page.store',    'uses' => 'App\Http\Controllers\Contract\Page\PageController@store']);
        Route::post('{id}/publish/page',  ['as' => 'contract.page.publish',  'uses' => 'App\Http\Controllers\Contract\Page\PageController@publish']);
        Route::post('{id}/output',        ['as' => 'contract.output.save',   'uses' => 'App\Http\Controllers\Contract\ContractController@saveOutputType']);
        Route::post('{id}/status',        ['as' => 'contract.status',        'uses' => 'App\Http\Controllers\Contract\ContractController@updateStatus']);
        Route::post('{id}/status/comment',['as' => 'contract.status.comment','uses' => 'App\Http\Controllers\Contract\ContractController@contractComment']);
        Route::post('{id}/publish',       ['as' => 'contract.publish',       'uses' => 'App\Http\Controllers\Contract\ContractController@publish']);
        Route::post('{id}/unpublish',     ['as' => 'contract.unpublish',     'uses' => 'App\Http\Controllers\Contract\ContractController@unPublish']);
        Route::get('{id}/comment',        ['as' => 'contract.comment.list',  'uses' => 'App\Http\Controllers\Contract\Comment\CommentController@index']);
        Route::get('{id}/metadata',       ['as' => 'contract.metadata',      'uses' => 'App\Http\Controllers\Contract\ContractController@getMetadata']);
        Route::get('{id}/page',           ['as' => 'contract.page.get',      'uses' => 'App\Http\Controllers\Contract\Page\PageController@getText']);
        Route::post('{id}/pages/search',  ['as' => 'contract.page.search',   'uses' => 'App\Http\Controllers\Contract\Page\PageController@search']);
        Route::get('{id1}/{id2}/compare', ['as' => 'contract.page.compare',  'uses' => 'App\Http\Controllers\Contract\Page\PageController@compare']);
        Route::get('{id}/allpage',        ['as' => 'contract.allpage.get',   'uses' => 'App\Http\Controllers\Contract\Page\PageController@getAllText']);
        Route::get('{id}/annotate',       ['as' => 'contract.annotate',      'uses' => 'App\Http\Controllers\Contract\Page\PageController@annotate']);
        Route::get('{id}/review',         ['as' => 'contract.review',        'uses' => 'App\Http\Controllers\Contract\Page\PageController@review']);
        Route::get('{id}/{lang}',         ['as' => 'contract.show.trans',    'uses' => 'App\Http\Controllers\Contract\ContractController@show']);

        // Discussion
        Route::get('discussion/{id}/{type}/{key}',  ['as' => 'contract.discussion',        'uses' => 'App\Http\Controllers\Contract\Discussion\DiscussionController@index']);
        Route::post('discussion/{id}/{type}/{key}', ['as' => 'contract.discussion.create', 'uses' => 'App\Http\Controllers\Contract\Discussion\DiscussionController@create']);

        // Utility / maintenance
        Route::get('select/type',            ['as' => 'contract.select.type',  'uses' => 'App\Http\Controllers\Contract\ContractController@contractType']);
        Route::any('generate/name',          ['as' => 'contract.generate.name','uses' => 'App\Http\Controllers\Contract\ContractController@getContractName']);
        Route::get('backup/meta/data',       ['as' => 'contract.backup.meta.data',  'uses' => 'App\Http\Controllers\Contract\ContractController@backupMetadata']);
        Route::get('update/meta/data',       ['as' => 'contract.update.meta.data',  'uses' => 'App\Http\Controllers\Contract\ContractController@updateMetadata']);
        Route::get('restore/meta/data',      ['as' => 'contract.restore.meta.data', 'uses' => 'App\Http\Controllers\Contract\ContractController@restoreMetadata']);

        // Elasticsearch maintenance routes
        Route::get('elasticsearch/update/published_at',                                   ['as' => 'contract.elasticsearch.update.published_at',         'uses' => 'App\Http\Controllers\Contract\ContractController@updatePublishedAtIndex']);
        Route::get('elasticsearch/update/annotation_category/community_consultation',     ['as' => 'contract.elasticsearch.update.annotation_category',   'uses' => 'App\Http\Controllers\Contract\ContractController@updateAnnotationCategory']);
        Route::get('elasticsearch/update/cluster',                                        ['as' => 'contract.elasticsearch.update.cluster',               'uses' => 'App\Http\Controllers\Contract\ContractController@updateAnnotationCluster']);
        Route::get('elasticsearch/restore/cluster/{key}',                                 ['as' => 'contract.elasticsearch.restore.cluster',              'uses' => 'App\Http\Controllers\Contract\ContractController@restoreAnnotationCluster']);
        Route::get('elasticsearch/update/supporting-doc/master/paginated',                ['as' => 'contract.elasticsearch.supporting-doc.paginated',     'uses' => 'App\Http\Controllers\Contract\ContractController@getPaginatedMasterIndex']);
        Route::get('elasticsearch/update/supporting-doc/master/{page}',                  ['as' => 'contract.elasticsearch.supporting-doc.master',        'uses' => 'App\Http\Controllers\Contract\ContractController@updateMasterIndex']);
        Route::get('elasticsearch/update/supporting-doc/parent-child',                   ['as' => 'contract.elasticsearch.supporting-doc.parent-child',  'uses' => 'App\Http\Controllers\Contract\ContractController@updateParentChildDocIndex']);
        Route::get('elasticsearch/update/supporting-doc/child-parent',                   ['as' => 'contract.elasticsearch.supporting-doc.child-parent',  'uses' => 'App\Http\Controllers\Contract\ContractController@updateChildParentDocIndex']);

        // Import
        Route::get('import',                    ['as' => 'contract.import',          'uses' => 'App\Http\Controllers\Contract\Import\ImportController@index']);
        Route::post('import',                   ['as' => 'contract.import.post',     'uses' => 'App\Http\Controllers\Contract\Import\ImportController@importPost']);
        Route::get('import/confirm/{key}',      ['as' => 'contract.import.confirm',  'uses' => 'App\Http\Controllers\Contract\Import\ImportController@Confirm']);
        Route::post('import/confirm/{key}',     ['as' => 'contract.import.confirm.post', 'uses' => 'App\Http\Controllers\Contract\Import\ImportController@confirmPost']);
        Route::get('import/status/{key}',       ['as' => 'contract.import.status',   'uses' => 'App\Http\Controllers\Contract\Import\ImportController@status']);
        Route::delete('import/delete/{key}',    ['as' => 'contract.import.delete',   'uses' => 'App\Http\Controllers\Contract\Import\ImportController@delete']);
        Route::get('import/deletefile/{key}',   ['as' => 'contract.import.deletefile','uses' => 'App\Http\Controllers\Contract\Import\ImportController@delete']);
        Route::get('import/notify/{key}',       ['as' => 'contract.import.notify',   'uses' => 'App\Http\Controllers\Contract\Import\ImportController@notify']);
    });

    /*
    |--------------------------------------------------------------------------
    | Annotations (API)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        Route::get('annotations/{contractId}', ['as' => 'contract.annotations', 'uses' => 'App\Http\Controllers\Annotation\Api\ApiController@getContractAnnotations']);
        Route::put('annotations/{id}',         'App\Http\Controllers\Annotation\Api\ApiController@save');
        Route::put('annotations',              'App\Http\Controllers\Annotation\Api\ApiController@save');
        Route::post('annotations',             'App\Http\Controllers\Annotation\Api\ApiController@save');
        Route::get('search',                   'App\Http\Controllers\Annotation\Api\ApiController@search');
        Route::post('annotations/{id}',        ['as' => 'annotation.delete', 'uses' => 'App\Http\Controllers\Annotation\Api\ApiController@delete']);
        Route::post('/annotation/update',      ['as' => 'annotation.update', 'uses' => 'App\Http\Controllers\Annotation\Api\ApiController@update']);
        Route::post('annotation/{id}/delete',  ['as' => 'annotation.delete', 'uses' => 'App\Http\Controllers\Annotation\Api\ApiController@delete']);
    });

    Route::get('/contract/{id}/annotations/list',   ['as' => 'contract.annotations.list',   'uses' => 'App\Http\Controllers\Annotation\AnnotationController@show']);
    Route::post('/contract/{id}/annotations/status',['as' => 'contract.annotations.status', 'uses' => 'App\Http\Controllers\Annotation\AnnotationController@updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */
    Route::get('user',                 ['as' => 'user.list',       'uses' => 'App\Http\Controllers\User\UserController@index']);
    Route::get('user/create',          ['as' => 'user.create',     'uses' => 'App\Http\Controllers\User\UserController@create']);
    Route::post('user/store',          ['as' => 'user.store',      'uses' => 'App\Http\Controllers\User\UserController@store']);
    Route::get('user/{id}/edit',       ['as' => 'user.edit',       'uses' => 'App\Http\Controllers\User\UserController@edit']);
    Route::patch('user/{id}/update',   ['as' => 'user.update',     'uses' => 'App\Http\Controllers\User\UserController@update']);
    Route::delete('user/{id}/delete',  ['as' => 'user.destroy',    'uses' => 'App\Http\Controllers\User\UserController@delete']);
    Route::post('user/{id}/deactivate',['as' => 'user.deactivate', 'uses' => 'App\Http\Controllers\User\UserController@deactivate']);
    Route::get('profile',              ['as' => 'user.profile',     'uses' => 'App\Http\Controllers\User\ProfilesController@profile']);
    Route::get('profile/edit',         ['as' => 'user.editProfile', 'uses' => 'App\Http\Controllers\User\ProfilesController@editProfile']);
    Route::patch('profile/update',     ['as' => 'profile.update',   'uses' => 'App\Http\Controllers\User\ProfilesController@updateUser']);

    /*
    |--------------------------------------------------------------------------
    | Code Lists
    |--------------------------------------------------------------------------
    */
    Route::get('codelist/{type}',            ['as' => 'codelist.list',    'uses' => 'App\Http\Controllers\CodeList\CodeListController@index']);
    Route::get('codelist/create/{type}',     ['as' => 'codelist.create',  'uses' => 'App\Http\Controllers\CodeList\CodeListController@create']);
    Route::post('codelist/store',            ['as' => 'codelist.store',   'uses' => 'App\Http\Controllers\CodeList\CodeListController@store']);
    Route::get('codelist/{type}/{id}/edit',  ['as' => 'codelist.edit',    'uses' => 'App\Http\Controllers\CodeList\CodeListController@edit']);
    Route::patch('codelist/{id}/update',     ['as' => 'codelist.update',  'uses' => 'App\Http\Controllers\CodeList\CodeListController@update']);
    Route::delete('codelist/{id}/{type}/delete', ['as' => 'codelist.destroy', 'uses' => 'App\Http\Controllers\CodeList\CodeListController@delete']);

    /*
    |--------------------------------------------------------------------------
    | Activity Log
    |--------------------------------------------------------------------------
    */
    Route::get('activities', ['as' => 'activitylog.index', 'uses' => 'App\Http\Controllers\ActivityLog\ActivityLogController@index']);

    /*
    |--------------------------------------------------------------------------
    | Quality
    |--------------------------------------------------------------------------
    */
    Route::get('quality', ['as' => 'quality.index', 'uses' => 'App\Http\Controllers\Quality\QualityController@index']);

    /*
    |--------------------------------------------------------------------------
    | Utility
    |--------------------------------------------------------------------------
    */
    Route::get('utility',               ['as' => 'utility.index',      'uses' => 'App\Http\Controllers\Utility\UtilityController@index']);
    Route::post('utility',              ['as' => 'utility.submit',     'uses' => 'App\Http\Controllers\Utility\UtilityController@save']);
    Route::get('download/text/{file}',  ['as' => 'bulk.text.download', 'uses' => 'App\Http\Controllers\Utility\UtilityController@bulkTextDownload']);

    /*
    |--------------------------------------------------------------------------
    | Disclosure
    |--------------------------------------------------------------------------
    */
    Route::get('disclosure', ['as' => 'disclosure.index', 'uses' => 'App\Http\Controllers\Disclosure\DisclosureController@index']);

    /*
    |--------------------------------------------------------------------------
    | MTurk
    |--------------------------------------------------------------------------
    */
    Route::get('mturk/public',                               'App\Nrgi\Mturk\Controllers\MTurkController@publicPage');
    Route::get('/mturk',                                     ['as' => 'mturk.index',          'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@index']);
    Route::get('/mturk/tasks',                               ['as' => 'mturk.allTasks',       'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@allTasks']);
    Route::post('/mturk/add/{contract_id}/tasks',            ['as' => 'mturk.add',            'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@createTasks']);
    Route::get('/mturk/activity/',                           ['as' => 'mturk.activity',       'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@activity']);
    Route::get('/mturk/reset/',                              ['as' => 'mturk.task.reset.cmd', 'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@resetHitCmd']);
    Route::get('/mturk/restore/',                            ['as' => 'mturk.task.restore.cmd','uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@restoreHitCmd']);
    Route::get('/mturk/{contract_id}',                       ['as' => 'mturk.tasks',          'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@tasksList']);
    Route::post('/mturk/{contract_id}/copy',                 ['as' => 'mturk.contract.copy',  'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@sendToRC']);
    Route::post('/mturk/{contract_id}/all/approve',          ['as' => 'mturk.task.approveAll','uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@approveAll']);
    Route::get('/mturk/{contract_id}/{task_id}',             ['as' => 'mturk.task.detail',    'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@taskDetail']);
    Route::post('/mturk/{contract_id}/{task_id}/approve',    ['as' => 'mturk.task.approve',   'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@approve']);
    Route::post('/mturk/{contract_id}/{task_id}/reject',     ['as' => 'mturk.task.reject',    'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@reject']);
    Route::post('/mturk/{contract_id}/{task_id}/reset',      ['as' => 'mturk.task.reset',     'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@resetHit']);
    Route::post('/mturk/{contract_id}/{task_id}/approved/reset', ['as' => 'mturk.task.approved.reset', 'uses' => 'App\Nrgi\Mturk\Controllers\MTurkController@resetApprovedHit']);
});
