<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'App\Nrgi\Repositories\User\UserRepositoryInterface',
            'App\Nrgi\Repositories\User\UserRepository'
        );
    }
}
