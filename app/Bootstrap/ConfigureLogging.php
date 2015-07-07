<?php namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseConfigureLogging;
use App\Nrgi\Log\NrgiWriter;
use Monolog\Handler\LogEntriesHandler;
use Monolog\Logger as Monolog;

/**
 * Custom Logger
 *
 * Class ConfigureLogging
 * @package App\Bootstrap
 */
class ConfigureLogging extends BaseConfigureLogging
{

    /**
     * Register the logger instance in the container.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @return \Illuminate\Log\Writer
     */
    protected function registerLogger(Application $app)
    {
        $app->instance(
            'log',
            $log = new NrgiWriter(
                new Monolog($app->environment()), $app['events']
            )
        );

        return $log;
    }

    /**
     * Custom handler for integrating LogEntries
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @param NrgiWriter                                    $log
     */
    public function configureCustomHandler(Application $app, NrgiWriter $log)
    {
        if (env('APP_ENV') == 'production') {
            $handler = new LogEntriesHandler(env('LOGENTRIES_TOKEN'));
            $log->getMonolog()->pushHandler($handler);
        }

        $log->useDailyFiles(
            $app->storagePath().'/logs/laravel.log',
            $app->make('config')->get('app.log_max_files', 5)
        );
    }
}
