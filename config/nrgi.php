<?php
return [
    /**
     * Roles for different type of users.
     */
    'roles'            => [
        'superadmin'         => [
            'name'         => 'superadmin',
            'display_name' => 'Admin',
            'description'  => 'NRGI staff, CCSI - Columbia university'
        ],
        'researcher'         => [
            'name'         => 'researcher',
            'display_name' => 'Research Associate',
            'description'  => '(graduate student, interns) to upload contract, provide metadata, annotate. Should be able to edit the data entered by others.'
        ],
        'country-admin'      => [
            'name'         => 'country-admin',
            'display_name' => 'Country Admin',
            'description'  => 'Country specific admin'
        ],
        'country-researcher' => [
            'name'         => 'country-researcher',
            'display_name' => 'Country Researcher',
            'description'  => 'Country specific researcher'
        ],
    ],
    'permissions'      => [
        'add-contract'           => [
            'name'         => 'add-contract',
            'display_name' => 'Add Contract',
            'description'  => 'Add new contract'
        ],
        'edit-contract'          => [
            'name'         => 'edit-contract',
            'display_name' => 'Edit Contract',
            'description'  => 'Edit a contract'
        ],
        'delete-contract'        => [
            'name'         => 'delete-contract',
            'display_name' => 'Delete Contract',
            'description'  => 'Delete a contract'
        ],
        'complete-metadata'      => [
            'name'         => 'complete-metadata',
            'display_name' => 'Complete metadata',
            'description'  => 'Complete contract metadata'
        ],
        'reject-metadata'        => [
            'name'         => 'reject-metadata',
            'display_name' => 'Reject metadata',
            'description'  => 'Reject contract metadata'
        ],
        'edit-text'              => [
            'name'         => 'edit-text',
            'display_name' => 'Edit text',
            'description'  => 'Edit contract text'
        ],
        'complete-text'          => [
            'name'         => 'complete-text',
            'display_name' => 'Complete text',
            'description'  => 'Complete contract text'
        ],
        'publish-text'           => [
            'name'         => 'publish-text',
            'display_name' => 'Publish text',
            'description'  => 'Publish contract text'
        ],
        'reject-text'            => [
            'name'         => 'reject-text',
            'display_name' => 'Reject text',
            'description'  => 'Reject contract text'
        ],
        'publish-metadata'       => [
            'name'         => 'publish-metadata',
            'display_name' => 'Publish metadata',
            'description'  => 'Publish a metadata'
        ],
        'add-annotation'         => [
            'name'         => 'add-annotation',
            'display_name' => 'Add Contract',
            'description'  => 'Add new annotation'
        ],
        'edit-annotation'        => [
            'name'         => 'edit-annotation',
            'display_name' => 'Edit Annotation',
            'description'  => 'Edit a annotation'
        ],
        'delete-annotation'      => [
            'name'         => 'delete-annotation',
            'display_name' => 'Delete Annotation',
            'description'  => 'Delete a annotation'
        ],
        'complete-annotation'    => [
            'name'         => 'complete-annotation',
            'display_name' => 'Complete annotation',
            'description'  => 'Complete contract annotation'
        ],
        'reject-annotation'      => [
            'name'         => 'reject-annotation',
            'display_name' => 'Reject annotation',
            'description'  => 'Reject contract annotation'
        ],
        'publish-annotation'     => [
            'name'         => 'publish-annotation',
            'display_name' => 'Publish annotation',
            'description'  => 'Publish a annotation'
        ],
        'unpublished-metadata' => [
            'name'         => 'unpublished-metadata',
            'display_name' => 'Unpublish Metadata',
            'description'  => 'Unpublish Metadata'
        ],
        'unpublished-text' => [
            'name'         => 'unpublished-text',
            'display_name' => 'Unpublish Text',
            'description'  => 'Unpublish Text'
        ],
        'unpublished-annotation' => [
            'name'         => 'unpublished-annotation',
            'display_name' => 'Unpublish Annotation',
            'description'  => 'Unpublish Annotation'
        ]

    ],
    'pdf_structure'    => ['structured', 'scanned', 'encrypted'],
    'country_role'     => ['country-admin', 'country-researcher'],
    'annotation_stage' => ['draft', 'completed', 'rejected', 'published', 'unpublished'],
    'pdf_process_path' => env('PDF_PROCESSOR_PATH'),
    'permission'       => [
        'completed'   => 'complete',
        'rejected'    => 'reject',
        'published'   => 'publish',
        'draft'       => 'draft',
        'unpublished' => 'unpublished'
    ],
    'pdf_storage_url'  => '/data',
];
