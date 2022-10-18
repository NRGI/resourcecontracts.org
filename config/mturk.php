<?php

return [

    /*
     |--------------------------------------------------------------------------
     | AWS Root Account Keys
     |--------------------------------------------------------------------------
     |
     | Amazon Mechanical Turk requires the AWS Root account's keys--the API will
     | not accept keys from an IAM account. To get your Root keys, log into your
     | AWS console with the root account, not an IAM account. The root account
     | login will use an email instead of a username. The keys can then be found
     | in the IAM management console.
     |
     */
    'credentials'  => [
        'MTURK_ROOT_ACCESS_KEY_ID'     => env('MTURK_KEY'),
        'MTURK_ROOT_SECRET_ACCESS_KEY' => env('MTURK_SECRET'),
    ],
    /*
    |--------------------------------------------------------------------------
    | Mturk API Mode
    |--------------------------------------------------------------------------
    |
    | Default is sandbox mode, All API calls will go to the sandbox Amazon
    | Mechanical Turk site.
    |
    | MTURK_SECRET=false for production
    |
    */
    'sandbox_mode' => env('MTURK_SANDBOX', true),
    /*
     |--------------------------------------------------------------------------
     | Mturk Defaults
     |--------------------------------------------------------------------------
     |
     | These optional defaults are the default parameters used when making an API
     | call to Amazon Mechanical Turk. You can override defaults by passing in your
     | desired values in any of the create HIT functions.
     |
     | If your are making a sandbox request, then the 'defaults.sandbox' will
     | override those same values in 'defaults.production'.
     | NOTE: The HITTypeID and HITLayoutId are specific to the mode in which they
     | were created (i.e., if HITTypeID = 3D1RILWJRGDLL6ABBAYORICGCAGZEM in
     | sandbox mode, the HITTypeID in production mode will be different).
     |
     | For definitions of these parameters: http://docs.aws.amazon.com/AWSMechTurk/latest/AWSMturkAPI/ApiReference_CreateHITOperation.html
     |
     */
    'defaults'     => [
        'sandbox'    => [],

        'production' => [
            'Title'                       => 'Production Mturk Title',
            'Description'                 => 'Transcription of the scanned contract pdf',
            'MaxAssignments'              => 1,
            //'Keywords'                    => ['transcription', 'pdf', 'language'],
            'Reward'                      => 0.50,
            'LifetimeInSeconds'           => 5*24*60*60,
            'AssignmentDurationInSeconds' => 5*24*60*60,
            "AutoApprovalDelayInSeconds" =>  30*24*60*60,
            'TaskItemCount'              => 20,
            // 'QualificationRequirements'   =>array(
            //     [
            //         "QualificationTypeId"=>"00000000000000000040",
            //         "Comparator"=>"GreaterThan",
            //         "IntegerValues"=>[50]
            //     ],
            //     [
            //         "QualificationTypeId"=>"000000000000000000L0",
            //         "Comparator"=>"GreaterThan",
            //         "IntegerValues"=>[95]
            //     ]
            // )
        ],

    ],
    'minimumBalance' => '25',
    'hitRenewDay' => 21,
    'currencyCode' => '$'
];
