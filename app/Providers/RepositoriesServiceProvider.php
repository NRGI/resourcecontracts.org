<?php namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoriesServiceProvider
 * @package App\Providers
 */
class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        View::composer('*', function ($view) {
            $view->with('current_user', auth()->user());
        });

        $this->app->bind(
            'App\Nrgi\Repositories\User\UserRepositoryInterface',
            'App\Nrgi\Repositories\User\UserRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\ContractRepositoryInterface',
            'App\Nrgi\Repositories\Contract\ContractRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\Page\PageRepositoryInterface',
            'App\Nrgi\Repositories\Contract\Page\PageRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface',
            'App\Nrgi\Repositories\Contract\Annotation\AnnotationRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\ActivityLog\ActivityLogRepositoryInterface',
            'App\Nrgi\Repositories\ActivityLog\ActivityLogRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\Comment\CommentRepositoryInterface',
            'App\Nrgi\Repositories\Contract\Comment\CommentRepository'
        );
        $this->app->bind(
            'App\Nrgi\Mturk\Repositories\TaskRepositoryInterface',
            'App\Nrgi\Mturk\Repositories\TaskRepository'
        );
        $this->app->bind(
            'App\Nrgi\Mturk\Repositories\Activity\ActivityRepositoryInterface',
            'App\Nrgi\Mturk\Repositories\Activity\ActivityRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\Contract\Discussion\DiscussionRepositoryInterface',
            'App\Nrgi\Repositories\Contract\Discussion\DiscussionRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\CodeList\ContractType\ContractTypeRepositoryInterface',
            'App\Nrgi\Repositories\CodeList\ContractType\ContractTypeRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\CodeList\Resource\ResourceRepositoryInterface',
            'App\Nrgi\Repositories\CodeList\Resource\ResourceRepository'
        );
        $this->app->bind(
            'App\Nrgi\Repositories\CodeList\DocumentType\DocumentTypeRepositoryInterface',
            'App\Nrgi\Repositories\CodeList\DocumentType\DocumentTypeRepository'
        );
    }

}
