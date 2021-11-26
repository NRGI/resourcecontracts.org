<?php namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration as BaseConfigureLogging;
use App\Nrgi\Log\NrgiWriter;
use Monolog\Handler\LogEntriesHandler;
use Monolog\Handler\LogglyHandler;
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
            $handler = $this->logglyHandler();
            $log->getMonolog()->pushHandler($handler);
        }

        $log->useFiles($app->storagePath() . '/logs/laravel.log');
    }

    /**
     * LogEntriesHandler
     *
     * @return LogEntriesHandler
     */
    protected function logEntriesHandler()
    {
        return new LogEntriesHandler(env('LOG_TOKEN'));
    }

    /**
     * Loggly Handler
     *
     * @return LogglyHandler
     */
    protected function logglyHandler()
    {
        $handler = new LogglyHandler(env('LOG_TOKEN'));
        $handler->setTag('ResourceContracts');

        return $handler;
    }
}
