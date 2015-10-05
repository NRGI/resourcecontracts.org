<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Annotation;
use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\AnnotationService;
use App\Nrgi\Services\ElasticSearch\ElasticSearchService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

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

        if ($this->input->getOption('annotation')) {
            $this->publishAnnotations($contracts);

            return;
        }
        $this->publishContracts($contracts);
    }

    /**
     * Publish all contract annotations
     */
    public function publishAnnotations($contracts)
    {
        foreach ($contracts as $contract) {
            $this->publishAnnotation($contract);
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
        return [['annotation', null, InputOption::VALUE_NONE, 'publish annotation all contracts', null]];

    }

    /**
     * publish individual contract annotation
     * @param $contract
     */
    protected function publishAnnotation($contract)
    {
        if ($this->annotations->getStatus($contract->id) == Annotation::PUBLISHED) {
            $this->elastic->deleteAnnotations($contract->id);
            $this->elastic->postAnnotation($contract->id);
            $this->info(sprintf('Contract %s : Annotations Indexed.', $contract->id));
        }
    }

    /**
     * Publish text,metadata,annotation of all contracts
     * @param $contracts
     */
    protected function publishContracts($contracts)
    {
        foreach ($contracts as $contract) {
            if ($contract->metadata_status == Contract::STATUS_PUBLISHED) {
                $this->elastic->postMetadata($contract->id);
                $this->info(sprintf('Contract %s : Metadata Indexed.', $contract->id));
            }
            if ($contract->text_status == Contract::STATUS_PUBLISHED) {
                $this->elastic->postText($contract->id);
                $this->info(sprintf('Contract %s : Text Indexed.', $contract->id));
            }
            $this->publishAnnotation($contract);
        }
    }

}
