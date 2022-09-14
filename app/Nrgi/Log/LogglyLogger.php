<?php

namespace App\Nrgi\Log;

use MonologHandlerLogglyHandler;
use MonologLogger;

class LogglyLogger {

    public function __invoke($config) {
        if(env('APP_ENV') == 'production') {
            $logger = new Logger('ResourceContracts');
            $handler = new LogglyHandler(env('LOG_TOKEN'));
            $handler->setTag('ResourceContracts');
            $logger->pushHandler($handler, Logger::INFO );
            return $logger;
        }
    }
}