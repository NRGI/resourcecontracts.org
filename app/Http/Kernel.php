<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        // 'Illuminate\Foundation\Bootstrap\DetectEnvironment',
        // 'Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables',
        // 'Illuminate\Foundation\Bootstrap\LoadConfiguration',
        'App\Bootstrap\ConfigureLogging',
        // 'Illuminate\Foundation\Bootstrap\HandleExceptions',
        // 'Illuminate\Foundation\Bootstrap\RegisterFacades',
        // 'Illuminate\Foundation\Bootstrap\RegisterProviders',
        // 'Illuminate\Foundation\Bootstrap\BootProviders',
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
        'App\Http\Middleware\VerifyCsrfToken',
        'App\Http\Middleware\Localization',
        // // \App\Http\Middleware\EncryptCookies::class,
        // \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        // \Illuminate\Session\Middleware\StartSession::class,
        // // \Illuminate\Session\Middleware\AuthenticateSession::class,
        // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        // \App\Http\Middleware\VerifyCsrfToken::class,
        // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        // \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        // \App\Http\Middleware\Localization::class,
    ];

    //    /**
    //  * The application's route middleware groups.
    //  *
    //  * @var array
    //  */
    // protected $middlewareGroups = [
    //     'web' => [
    //         \App\Http\Middleware\EncryptCookies::class,
    //         \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    //         \Illuminate\Session\Middleware\StartSession::class,
    //         // \Illuminate\Session\Middleware\AuthenticateSession::class,
    //         \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //         \App\Http\Middleware\VerifyCsrfToken::class,
    //         \Illuminate\Routing\Middleware\SubstituteBindings::class,
    //         \App\Http\Middleware\Localization::class,
    //         \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //     ],

    //     'admin' => [
    //         \App\Http\Middleware\EncryptCookies::class,
    //         \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    //         \Illuminate\Session\Middleware\StartSession::class,
    //         // \Illuminate\Session\Middleware\AuthenticateSession::class,
    //         \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //         \App\Http\Middleware\VerifyCsrfToken::class,
    //         \Illuminate\Routing\Middleware\SubstituteBindings::class,
    //         \App\Http\Middleware\Localization::class,
    //         \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //     ],

    //     'api' => [
    //         'throttle:300,1',
    //         \Illuminate\Routing\Middleware\SubstituteBindings::class,
    //         \App\Http\Middleware\Localization::class,
    //         \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    //     ],
    // ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => 'App\Http\Middleware\Authenticate',
        'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
    ];
}
