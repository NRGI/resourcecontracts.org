<?php namespace App\Providers;

use Monolog\Logger;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Support\ServiceProvider;

class NRGILogServiceProvider extends LogServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance(
            'log',
            $log = new NrgiWriter(
                new Monolog($app->environment()), $app['events']
            )
        );
    }


     /**
     * Custom handler for integrating LogEntries
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @param NrgiWriter                                    $log
     */
    public function configureHandler(NrgiWriter $log)
    {
        if (env('APP_ENV') == 'production') {
            $handler = $this->logglyHandler();
            $log->getMonolog()->pushHandler($handler);
        }
        $log->useFiles($this->app->storagePath() . '/logs/laravel.log');
    }
}

?>