<?php
/*
|--------------------------------------------------------------------------
| Contract Metadata
|--------------------------------------------------------------------------
|
*/
return [
    'category'  => [
        'olc' => 'OpenLandContracts',
        'rc'  => 'ResourceContracts'
    ],
    'text_type' => [
        1 => ['name' => 'Acceptable', 'color' => 'green'],
        2 => ['name' => 'Needs editing', 'color' => 'yellow'],
        3 => ['name' => 'Needs full transcription', 'color' => 'red']
    ],
    'schema'    => [
        'file'     => '',
        'filehash' => '',
        'user_id'  => '',
        'metadata' => [
            'contract_name'          => '',
            'contract_identifier'    => '',
            'language'               => '',
            'country'                => [
                'code' => '',
                'name' => '',
            ],
            'resource'               => [],
            'government_entity'      => [
                [
                    'entity'     => '',
                    'identifier' => '',
                ]
            ],
            'type_of_contract'       => [],
            'signature_date'         => '',
            'document_type'          => '',
            'company'                => [
                [
                    'name'                          => '',
                    'participation_share'           => '',
                    'jurisdiction_of_incorporation' => '',
                    'registration_agency'           => '',
                    'company_founding_date'         => '',
                    'company_address'               => '',
                    'company_number'                => '',
                    'parent_company'                => '',
                    'open_corporate_id'             => '',
                    'operator'                      => ''
                ]
            ],
            'project_title'          => '',
            'project_identifier'     => '',
            'concession'             => [
                [
                    'license_name'       => '',
                    'license_identifier' => ''
                ]
            ],
            'source_url'             => '',
            'disclosure_mode'        => '',
            'date_retrieval'         => '',
            'category'               => [],
            'signature_year'         => '',
            'file_size'              => '',
            'open_contracting_id'    => '',
            'show_pdf_text'          => '',
            'is_supporting_document' => '',
            'contract_note'          => '',
            'deal_no'                => '',
            'matrix_page'            => '',

        ],
    ]
];
