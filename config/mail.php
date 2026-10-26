<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | MAIL_MAILER is the modern name; MAIL_DRIVER is still honoured for
    | existing deployments (see conf/env.template).
    |
    */

    'default' => env('MAIL_MAILER', env('MAIL_DRIVER', 'smtp')),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    */

    'mailers' => [
        'smtp' => [
            'transport'  => 'smtp',
            'host'       => env('MAIL_HOST', 'smtp.mailgun.org'),
            'port'       => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username'   => env('MAIL_USERNAME'),
            'password'   => env('MAIL_PASSWORD'),
            'timeout'    => null,
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path'      => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers'   => ['smtp', 'log'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('FROM_EMAIL', 'nrgi@yipl.com.np'),
        'name'    => env('MAIL_FROM_NAME', 'NRGI'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification recipients (comma separated lists)
    |--------------------------------------------------------------------------
    */

    'notify' => [
        'NOTIFY_MAIL'        => env('NOTIFY_MAIL'),
        'MTURK_NOTIFY_OLC'   => env('MTURK_NOTIFY_OLC'),
        'MTURK_NOTIFY_RC'    => env('MTURK_NOTIFY_RC'),
        'NOTIFY_ERROR_EMAIL' => env('NOTIFY_ERROR_EMAIL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Markdown Mail Settings
    |--------------------------------------------------------------------------
    */

    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];
