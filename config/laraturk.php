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
    'credentials' => [
        'AWS_ROOT_ACCESS_KEY_ID' => env('MTURK_KEY'),
        'AWS_ROOT_SECRET_ACCESS_KEY' => env('MTURK_SECRET'),
    ],

    /*
     |--------------------------------------------------------------------------
     | LaraTurk Defaults
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
     'defaults' => [

        'sandbox' => [
             // 'HITTypeId' => 'default HITTypeId',
             // 'HITLayoutId' => 'default HITLayoutId',
        ],

        'production' => [
             // 'HITTypeId' => 'default HITTypeId',
             // 'Title' => 'Default HIT title',
              'Description' => 'Default HIT description',
              'Keywords' => [ 'default', 'HIT', 'keywords' ],
             // 'HITLayoutId' => 'default HITLayoutId',
              'Reward' => [
              	'Amount' => '0.07',
              	'CurrencyCode' => 'USD'
              ],
              'AssignmentDurationInSeconds' => '300',
              'LifetimeInSeconds' => '3600',
             // 'AutoApprovalDelayInSeconds' => '60',
              'MaxAssignments' => '3',
              /*'QualificationRequirement' => [
              	[
              		'QualificationTypeId' => '2F1QJWKUDD8XADTFD2Q0G6UTO95ALH',
              		'Comparator' => 'Exists'
              	],
              ],*/
             // 'RequesterAnnotation' => 'default annotation for the requester',
        ],

     ],

];
