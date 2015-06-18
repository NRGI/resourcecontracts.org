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
        'researcher' => [
            'name'         => 'researcher',
            'display_name' => 'Research Associate',
            'description'  => '(graduate student, interns) to upload contract, provide metadata, annotate. Should be able to edit the data entered by others.'
        ],
    ],

    'permissions' =>[
        'add-contract' => [
            'name' => 'add-contract',
            'display_name' => 'Add Contract',
            'description' => 'Add new contract'
        ],

        'edit-contract' => [
            'name' => 'edit-contract',
            'display_name' => 'Edit Contract',
            'description' => 'Edit a contract'
        ],

        'delete-contract' => [
            'name' => 'delete-contract',
            'display_name' => 'Delete Contract',
            'description' => 'Delete a contract'
        ],

        'complete-metadata' => [
            'name' => 'complete-metadata',
            'display_name' => 'Complete metadata',
            'description' => 'Complete contract metadata'
        ],

        'reject-metadata' => [
            'name' => 'reject-metadata',
            'display_name' => 'Reject metadata',
            'description' => 'Reject contract metadata'
        ],

        'publish-metadata' => [
            'name' => 'publish-metadata',
            'display_name' => 'Publish metadata',
            'description' => 'Publish a metadata'
        ],

        'add-annotation' => [
            'name' => 'add-annotation',
            'display_name' => 'Add Contract',
            'description' => 'Add new annotation'
        ],

        'edit-annotation' => [
            'name' => 'edit-annotation',
            'display_name' => 'Edit Annotation',
            'description' => 'Edit a annotation'
        ],

        'delete-annotation' => [
            'name' => 'delete-annotation',
            'display_name' => 'Delete Annotation',
            'description' => 'Delete a annotation'
        ],

        'complete-annotation' => [
            'name' => 'complete-annotation',
            'display_name' => 'Complete annotation',
            'description' => 'Complete contract annotation'
        ],

        'reject-annotation' => [
            'name' => 'reject-annotation',
            'display_name' => 'Reject annotation',
            'description' => 'Reject contract annotation'
        ],

        'publish-annotation' => [
            'name' => 'publish-annotation',
            'display_name' => 'Publish annotation',
            'description' => 'Publish a annotation'
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
