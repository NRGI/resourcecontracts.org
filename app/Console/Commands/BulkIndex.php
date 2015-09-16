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
class BulkIndex extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:bulkindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index all the data to elasticsearch.';
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
            if ($contract->metadata_status == "published") {
                $this->elastic->postMetadata($contract->id);
                $this->info(sprintf('Contract %s : Metadata Indexed.', $contract->id));
            }
            if ($contract->text_status == "published") {
                $this->elastic->postText($contract->id);
                $this->info(sprintf('Contract %s : Text Indexed.', $contract->id));
            }
            if ($this->annotations->getStatus($contract->id) == "published") {
                $this->elastic->deleteAnnotations($contract->id);
                $this->elastic->postAnnotation($contract->id);
                $this->info(sprintf('Contract %s : Annotations Indexed.', $contract->id));
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
