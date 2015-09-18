<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\AnnotationService;
use App\Nrgi\Services\ElasticSearch\ElasticSearchService;
use Illuminate\Console\Command;


/**
 *  Command for Bulk index of data into elasticsearch
 *
 * Class BulkIndex
 * @package App\Console\Commands
 */
class SlugAnnotationCategory extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:slugannotationcategory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Slugify annotation category.';
    /**
     * @var ElasticSearchService
     */
    private $elastic;
    /**
     * @var AnnotationService
     */
    private $annotations;
    /**
     * @var Contract
     */
    private $contract;

    /**
     * Create a new command instance.
     *
     * @param ElasticSearchService $elastic
     * @param AnnotationService    $annotations
     * @param Contract             $contract
     */
    public function __construct(ElasticSearchService $elastic, AnnotationService $annotations, Contract $contract)
    {
        parent::__construct();
        $this->elastic     = $elastic;
        $this->annotations = $annotations;
        $this->contract    = $contract;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $contracts = $this->contract->all();
        foreach ($contracts as $contract) {

            foreach ($contract->annotations as $annotation) {
                $this->info("contractID => {$contract->id}");
                $annotationArray = json_encode($annotation->annotation);
                $annotationArray = json_decode($annotationArray, true);
                if ($annotationArray['category'] == "General information") {
                    $annotationArray['category'] = "i-general-information";
                }
                $annotationArray['category'] = str_slug(trim($annotationArray['category']), '-');
                $annotation->annotation      = $annotationArray;
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
