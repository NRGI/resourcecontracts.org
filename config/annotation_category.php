<?php

return [
    'annotation_category_schema' => [
        'i-general-information'              =>
            [
                'country',
                'name-of-company-executing-document',
                'legal-enterprise-identifier',
                'corporate-headquarters',
                'company-structure',
                'parent-company-or-affiliates-outside-of-country',
                'company-website',
                'type-of-contract',
                'project-title',
                'name-of-field-block-deposit-or-site',
                'location',
                'closest-community',
                'date-of-issue-of-titlepermit',
                'year-of-issue-of-titlepermit',
                'date-of-ratification',
                'other-general'
            ],
        '1-fundamental-provisions'           =>
            [
                'legal-enterprise-identifier',
                'signatories-company',
                'state-agency-national-company-or-ministry-executing-the-document',
                'signatories-state',
                'name-andor-composition-of-the-company-created',
                'date-contract-signature',
                'year-of-contract-signature',
                'term',
                'renewal-or-extension-of-term',
                'resources',
                'size-of-concession-area'
            ],
        '2-community-and-social-obligations' =>
            [
                'local-development-agreement',
                'sacred-cultural-or-historical-sites',
                'community-consultation',
                'training',
                'local-employment',
                'local-procurement',
                'resettlement',
                'outgrowers-program',
                'protections-or-benefits-for-employees-dependents-or-others',
                'physical-security-or-protection-of-property',
                'grievance-mechanisms',
                'right-to-access-concession-area-non-contracting-parties'
            ],
        '3-developers-financial-obligations' =>
            [
                'royalties',
                'income-tax-rate',
                'income-tax-exemptions',
                'income-tax-other',
                'production-share-cost-oil-features-basis-of-calculation-limits-on-cost-recovery-eg-as-of-revenue-or-production-capex-uplift-etc',
                'production-share-profit-oil-features-triggers-for-variations-in-split-irr-factor-production-etc',
                'service-agreement-fee-to-developer-or-contractor',
                'capital-gains-tax',
                'withholding-tax',
                'provisions-for-renewing-reserves',
                'investment-credit',
                'custom-duties',
                'social-security-contributions-by-employer',
                'surface-fees-or-rent',
                'financial-obligations-community-or-commodity-funds',
                'carbon-credits',
                'bonuses',
                'state-participation',
                'audit-mechanisms-financial-obligations',
                'restrictions-on-transactions-with-affiliated-parties',
                'other-financialfiscal'
            ],
        '4-environmental-provisions'         =>
            [
                'environmental-impact-assessment-and-management-plan',
                'environmental-monitoring',
                'socialhuman-rights-impact-assessment-and-management-plan',
                'socialhuman-rights-monitoring',
                'water-use',
                'environmental-protections'
            ],
        '5-operational-provisions'           =>
            [
                'work-and-investment-commitments',
                'transfer-of-risk',
                'infrastructure',
                'infrastructure-third-party-use',
                'value-addition-or-downstream-activities',
                'land-use-outside-of-concession-area',
                'other-operational'
            ],
        '6-miscellaneous-provisions'         =>
            [
                'governing-law',
                'arbitration-and-dispute-resolution',
                'stabilization-clause',
                'assignment-or-transfer',
                'cancellation-or-termination',
                'confidentiality',
                'language',
                'reporting-requirements',
                'hardship-clause-or-force-majeure',
                'expropriation-or-nationalization',
                'other-miscellaneous'
            ],
        'iii-document-notes'                 =>
            [
                'pages-missing-from-copy',
                'annexes-missing-from-copy'
            ]
    ]
];