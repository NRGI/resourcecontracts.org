<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\AnnotationService;
use Illuminate\Console\Command;


/**
 * Updates annotation category
 *
 * Class UpdateAnnotation
 * @package App\Console\Commands
 */
class UpdateAnnotation extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updateannotation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update annotation category.';
    /**
     * @var AnnotationService
     */
    protected $annotations;
    /**
     * @var Contract
     */
    protected $contract;

    /**.
     * @param AnnotationService $annotations
     * @param Contract          $contract
     */
    public function __construct(AnnotationService $annotations, Contract $contract)
    {
        parent::__construct();
        $this->annotations = $annotations;
        $this->contract    = $contract;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $annotation_mapping_category = [
            'i-general-information',
            '1-fundamental-provisions',
            '2-community-and-social-obligations',
            '3-developers-financial-obligations',
            '4-environmental-provisions',
            '5-operational-provisions',
            '6-miscellaneous-provisions',
            'iii-document-notes',
            'ii-summary-of-terms',
            'environmental-monitoring'
        ];
        $contracts                   = $this->contract->all();
        foreach ($contracts as $contract) {
            foreach ($contract->annotations as $annotation) {
                $this->info("contractID => {$contract->id}");
                $annotationArray = json_encode($annotation->annotation);
                $annotationArray = json_decode($annotationArray, true);
                $category_mapped = array_search($annotationArray['category'], $annotation_mapping_category);
                if (false !== $category_mapped) {
                    $annotationArray['category'] = 'other';
                }
                $annotationArray['cluster'] = _l(config("annotation_category.cluster.{$annotationArray['category']}"));
                $annotation->annotation     = $annotationArray;
                $this->info($annotation->save());
            }
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}
