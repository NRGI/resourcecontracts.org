<?php
return [
    /**
     * Roles for different type of users.
     */
    'roles'            => [
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
    'pdf_structure'    => ['structured', 'scanned'],
    'pdf_process_path' => env('PDF_PROCESSOR_PATH'),
    'annotation_tags'  => [
        "Country",
        "Local-Company-Name",
        "Legal-Enterprise-Identifier",
        "Corporate-headquarters",
        "Company-structure",
        "Parent-companies-or-affilates",
        "Company-website",
        "Type-of-document",
        "Project-title",
        "Name/number-of-field-block-or-deposit",
        "Location-longitude-and-latitude",
        "Closest-community",
        "Date-of-issue-of-title/permit",
        "Date-of-ratification",
        "Stabilization-clause",
        "Arbitration-and-dispute-resolution"
    ]
];
