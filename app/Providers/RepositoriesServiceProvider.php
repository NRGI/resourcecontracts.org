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
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\ContractRepositoryInterface',
            'App\Nrgi\Repositories\Contract\ContractRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\Pages\PagesRepositoryInterface',
            'App\Nrgi\Repositories\Contract\Pages\PagesRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\AnnotationRepositoryInterface',
            'App\Nrgi\Repositories\Contract\AnnotationRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\ActivityLog\ActivityLogRepositoryInterface',
            'App\Nrgi\Repositories\ActivityLog\ActivityLogRepository'
        );
    }

}
