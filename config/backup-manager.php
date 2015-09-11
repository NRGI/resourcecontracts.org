<?php

return [
    'local' => [
        'type' => 'Local',
        'root' => storage_path('app'),
    ],
    's3' => [
        'type' => 'AwsS3',
        'key'    => 'AKIAIXHGIWERCKRUKBTA',
        'secret' => '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',
        'region' => 'us-west-2',
        'bucket' => 'rc-stage-db-backups',
        'root'   => '',
        'timeout'=>0,
    ],
];