<?php
return [
    /**
     * Roles for different type of users.
     */
    'roles'    => [
        'superadmin'         => [
            'name'         => 'superadmin',
            'display_name' => 'Super Administrator',
            'description'  => 'NRGI staff, CCSI - Columbia university'
        ],
        'admin'              => [
            'name'         => 'admin',
            'display_name' => 'Country/national Administrator',
            'description'  => 'National administrator should be able to approve the data uploaded by the research associate.'
        ],
        'research_associate' => [
            'name'         => 'research_associate',
            'display_name' => 'Research Associate',
            'description'  => '(graduate student, interns) to upload contract, provide metadata, annotate. Should be able to edit the data entered by others.'
        ],
    ],
    'country'  => [
        'Afghanistan' => 'Afghanistan',
        'Albania'     => 'Albania',
        'Algeria'     => 'Algeria',
        'Andorra'     => 'Andorra',
        'Angola'      => 'Angola'
    ],
    'language' => [
        'EN' => 'English',
        'FR' => 'French',
        'AL' => 'Algeria',
        'An' => 'Andorra',
        'Ag' => 'Angola'
    ],
    'resource' => [
        'resource1' => 'resource1',
        'resource2' => 'resource2',
    ]
];