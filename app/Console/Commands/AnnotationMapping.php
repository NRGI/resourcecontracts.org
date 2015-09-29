<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Annotation;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class AnnotationMapping extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:annotationmapping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Map annotation category.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $contract_id = $this->input->getOption('id');
        $this->updateAnnotationCategory($contract_id);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['id', null, InputOption::VALUE_OPTIONAL, 'ID of the contract', null],
        ];
    }

    /**
     * Update Annotation Category
     *
     * @param $contract_id
     */
    protected function updateAnnotationCategory($contract_id)
    {
        $annotations = $this->contractAnnotations($contract_id);
        foreach ($annotations as $annotation) {
            $this->info("contractID => {$annotation->contract_id}, annotation => {$annotation->id}");
            $annotationArray = json_encode($annotation->annotation);
            $annotationArray = json_decode($annotationArray, true);
            $annotationArray['category'] = $this->mapAnnotationCategory($annotation->category);
            $annotation->annotation      = $annotationArray;
            $this->info($annotation->save());
        }
    }

    protected function contractAnnotations($contract_id)
    {
        if (is_null($contract_id)) {
            $result = Annotation::selectRaw("id, annotation->>'category' as category, contract_id, annotation")->get();
        } else {
            $result = Annotation::selectRaw("id, annotation->>'category' as category, contract_id, annotation")->whereRaw('contract_id =' . $contract_id)->get();
        }

        return $result;
    }


    /**
     * Annotation category mapping
     *      *
     * @param $category
     * @return string
     */
    protected function mapAnnotationCategory($category)
    {
        $catList = [
            "production-share-profit-oil-features"                                                                                        => 'production-share-profit-oil-features-triggers-for-variations-in-split-irr-factor-production-etc',
            "social-environmental-andor-human-rights-impact-assessments-as-well-as-related-management-plans"                              => '',
            "other-financial-fiscal"                                                                                                      => 'other-financialfiscal',
            "physical-security-protection-of-property-andor-use-of-guards"                                                                => 'physical-security-or-protection-of-property',
            "local-company-name"                                                                                                          => '',
            "other"                                                                                                                       => 'Other',
            "local-employment-requirements"                                                                                               => 'local-employment',
            "state-agency-national-company-or-ministry-executing-document"                                                                => 'state-agency-national-company-or-ministry-executing-the-document',
            "type-of-document-right-concession-lease-production-sharing-agreement-service-agreement-etc"                                  => 'type-of-contract',
            "mining-tax-royalty-tax"                                                                                                      => '',
            "right-to-take-andor-use-water-withinnear-contract-concession-area-including-fees-licenses-and-permits-required"              => '',
            "Highlight"                                                                                                                   => '',
            "date-issue-of-title-or-permit"                                                                                               => '',
            "construction-and-maintenance-of-infrastructure"                                                                              => '',
            "right-to-access-concession-areanon-contracting-parties"                                                                      => '',
            "general-information"                                                                                                         => '',
            "other-exemptions"                                                                                                            => '',
            "social-human-rights-monitoring"                                                                                              => '',
            "local-procurement-requirements"                                                                                              => '',
            "social-human-rights-impact-assessment-and-management-plan"                                                                   => '',
            "parent-companies-or-affilates-outside-of-the-country-if-different-from-the-above-mentioned-and-their-corporate-headquarters" => '',
            "location-longitude-and-latitude-onshore-vs-offshore-shallow-vs-deep"                                                         => '',
            "third-party-use-of-infrastructure"                                                                                           => '',
            "production-share-cost-oil-features"                                                                                          => '',
            "name-andor-composition-of-company-created"                                                                                   => '',
            "name-of-company-executing-the-document-and-composition-of-the-shareholders"                                                  => '',
            "audit"                                                                                                                       => '',
            "type-of-resources-mineral-type-crude-oil-gas-timber-etc-or-specific-crops-planned-ex-food-crops-oil-palm-etc"                => '',
            "other-requirements-regarding-protection-of-the-environment-including-prevention-of-pollution-and-watercourse-protection"     => '',
            "state-agency-national-company-ministry-executing-the-document"                                                               => 'state-agency-national-company-or-ministry-executing-the-document',
            "reporting-requirements-and-other-methods-of-monitoring-compliance"                                                           => '',
            "water-user"                                                                                                                  => 'maile change gareko wala mappign data',
            "requirements-for-community-consultation"                                                                                     => ''
        ];

        if (array_key_exists($category, $catList)) {
            return $catList[$category];
        } else {
            return $category;
        }
    }
}
