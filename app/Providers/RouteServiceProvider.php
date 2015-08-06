<?php namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
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
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
            require app_path('Http/Routes/import.php');
            require app_path('Http/Routes/contract.php');
            require app_path('Http/Routes/annotation.php');
            require app_path('Http/Routes/activitylog.php');
            require app_path('Http/Routes/user.php');
        });

        $router->group(['namespace' => $this->mturk_namespace], function ($router) {
            require app_path('Nrgi/Mturk/routes.php');
        });
    }
}
