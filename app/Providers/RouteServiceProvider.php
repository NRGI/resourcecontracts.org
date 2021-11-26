<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';
    /**
     * This namespace is applied to the controller routes for Mechanical turk.
     *
     * @var string
     */
    protected $mturk_namespace = 'App\Nrgi\Mturk\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    public const HOME = '/home';

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        Route::group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
            require app_path('Http/Routes/import.php');
            require app_path('Http/Routes/contract.php');
            require app_path('Http/Routes/annotation.php');
            require app_path('Http/Routes/activitylog.php');
            require app_path('Http/Routes/user.php');
            require app_path('Http/Routes/quality.php');
            require app_path('Http/Routes/utility.php');
            require app_path('Http/Routes/disclosure.php');
            require app_path('Http/Routes/codelist.php');
        });

        Route::group(['namespace' => $this->mturk_namespace], function ($router) {
            require app_path('Nrgi/Mturk/routes.php');
        });

        //
    }

    // /**
    //  * Define the routes for the application.
    //  *
    //  * @return void
    //  */
    // public function mapWebRoutes()
    // {
    //     Route::group(['namespace' => $this->namespace], function ($router) {
    //         require app_path('Http/routes.php');
    //     });
    // }

    // /**
    //  * Define the routes for the application.
    //  *
    //  * @return void
    //  */
    // public function mapAdminRoutes()
    // {
    //     Route::group(['namespace' => $this->namespace], function ($router) {
    //         require app_path('Http/Routes/import.php');
    //         require app_path('Http/Routes/contract.php');
    //         require app_path('Http/Routes/annotation.php');
    //         require app_path('Http/Routes/activitylog.php');
    //         require app_path('Http/Routes/user.php');
    //         require app_path('Http/Routes/quality.php');
    //         require app_path('Http/Routes/utility.php');
    //         require app_path('Http/Routes/disclosure.php');
    //         require app_path('Http/Routes/codelist.php');
    //     });

    //     Route::group(['namespace' => $this->mturk_namespace], function ($router) {
    //         require app_path('Nrgi/Mturk/routes.php');
    //     });
    // }
}