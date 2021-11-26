<?php

namespace App\Providers;

use App\Nrgi\Services\Language\LanguageService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            'App\Services\Registrar'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $data = [
                 'current_user' => auth()->user(),
                 'lang' => app(LanguageService::class)
            ];
             $view->with($data);
         });
 
         if(Request::isSecure()){
             URL::forceSchema('https');
         }
    }
}