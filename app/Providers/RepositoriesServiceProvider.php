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
            \App\Nrgi\Repositories\User\UserRepositoryInterface::class,
            \App\Nrgi\Repositories\User\UserRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\Contract\ContractRepositoryInterface::class,
            \App\Nrgi\Repositories\Contract\ContractRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\Contract\Page\PageRepositoryInterface::class,
            \App\Nrgi\Repositories\Contract\Page\PageRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\Contract\Annotation\AnnotationRepositoryInterface::class,
            \App\Nrgi\Repositories\Contract\Annotation\AnnotationRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\ActivityLog\ActivityLogRepositoryInterface::class,
            \App\Nrgi\Repositories\ActivityLog\ActivityLogRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\Contract\Comment\CommentRepositoryInterface::class,
            \App\Nrgi\Repositories\Contract\Comment\CommentRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Mturk\Repositories\TaskRepositoryInterface::class,
            \App\Nrgi\Mturk\Repositories\TaskRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Mturk\Repositories\Activity\ActivityRepositoryInterface::class,
            \App\Nrgi\Mturk\Repositories\Activity\ActivityRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\Contract\Discussion\DiscussionRepositoryInterface::class,
            \App\Nrgi\Repositories\Contract\Discussion\DiscussionRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\CodeList\ContractType\ContractTypeRepositoryInterface::class,
            \App\Nrgi\Repositories\CodeList\ContractType\ContractTypeRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\CodeList\Resource\ResourceRepositoryInterface::class,
            \App\Nrgi\Repositories\CodeList\Resource\ResourceRepository::class
        );
        $this->app->bind(
            \App\Nrgi\Repositories\CodeList\DocumentType\DocumentTypeRepositoryInterface::class,
            \App\Nrgi\Repositories\CodeList\DocumentType\DocumentTypeRepository::class
        );
    }

}
