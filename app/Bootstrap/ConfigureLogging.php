<?php namespace App\Bootstrap;

use App\Nrgi\Entities\ActivityLog\ActivityLog;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Bootstrap\ConfigureLogging as BaseConfigureLogging;
use App\Nrgi\Log\NrgiWriter;
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
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return \Illuminate\Log\Writer
     */
    protected function registerLogger(Application $app)
    {
        $app->instance('log', $log = new NrgiWriter(
            new Monolog($app->environment()), $app['events'])
        );

        return $log;
    }
}
