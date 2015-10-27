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
    protected $elastic;
    /**
     * @var AnnotationService
     */
    protected $annotations;
    /**
     * @var Contract
     */
    protected $contract;

    /**
     * @var contractCount
     */
    protected $contractCount;

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
        $this->info("getting contracts");
        $contracts           = $this->getContracts();
        $this->contractCount = $contracts->count();
        $this->info("no of contract to publish =>{$this->contractCount}");
        $this->info("publishing...");
        if ($this->input->getOption('annotation')) {
            $this->publishAnnotations($contracts);

            return;
        }

        if ($this->input->getOption('metadata')) {
            $this->publishAllMetadata($contracts);

            return;
        }

        if ($this->input->getOption('text')) {
            $this->publishAllText($contracts);

            return;
        }

        $this->publishContracts($contracts);
    }

    /**
     * Publish all contract annotations
     * @param $contracts
     */
    public function publishAnnotations($contracts)
    {
        $index = 0;
        foreach ($contracts as $contract) {
            $this->publishAnnotation($contract);
            $this->status($index ++);
        }

    }

    /**
     * Publish all contract Metadata
     * @param $contracts
     */
    public function publishAllMetadata($contracts)
    {
        $index = 0;
        foreach ($contracts as $contract) {
            $this->publishMetadata($contract);
            $this->status($index ++);
        }

    }

    /**
     * Publish all contract Text
     * @param $contracts
     */
    public function publishAllText($contracts)
    {
        $index = 0;
        foreach ($contracts as $contract) {
            $this->publishText($contract);
            $this->status($index ++);
        }

    }

    /**
     * publish individual contract annotation
     * @param Contract $contract
     */
    protected function publishAnnotation(Contract $contract)
    {
        if ($this->annotations->getStatus($contract->id) == Annotation::PUBLISHED) {
            $this->elastic->deleteAnnotations($contract->id);
            $this->elastic->postAnnotation($contract->id);
            $this->info(sprintf('Contract %s : Annotations Indexed.', $contract->id));
        }
    }

    /**
     * publish individual contract text only
     * @param Contract $contract
     */
    protected function publishText(Contract $contract)
    {
        if ($contract->text_status == Contract::STATUS_PUBLISHED) {
            $this->elastic->postText($contract->id);
            $this->info(sprintf('Contract %s : Text Indexed.', $contract->id));
        }
    }

    /**
     * publish individual contract metadata only
     * @param Contract $contract
     */
    protected function publishMetadata(Contract $contract)
    {
        if ($contract->metadata_status == Contract::STATUS_PUBLISHED) {
            $this->elastic->postMetadata($contract->id);
            $this->info(sprintf('Contract %s : Metadata Indexed.', $contract->id));
        }
    }

    /**
     * Publish text,metadata,annotation of all contracts
     * @param $contracts
     */
    protected function publishContracts($contracts)
    {
        $index = 0;
        foreach ($contracts as $contract) {
            $this->publishMetadata($contract);
            $this->publishText($contract);
            $this->publishAnnotation($contract);
            $this->status($index ++);
        }
    }

    /**
     * @param $contracts
     * @param $index
     */
    protected function status($index)
    {
        $remaining = $this->contractCount - $index;
        $this->info("remaining contracts to be indexed =>{$remaining}");
    }

    /**
     * get contracts based on option category
     * @return Collection
     */
    protected function getContracts()
    {
        $category = $this->input->getOption('category');
        if (is_null($category)) {
            $contracts = $this->contract->all();

            return $contracts;
        }

        $query = $this->contract->select('*');
        $from  = "contracts ";
        $from .= ",json_array_elements(contracts.metadata->'category') cat";

        $query->whereRaw("trim(both '\"' from cat::text) = '" . $category . "'");
        $query->from(\DB::raw($from));
        $contracts = $query->get();

        return $contracts;
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
        return [
            ['annotation', null, InputOption::VALUE_NONE, 'publish annotation all contracts', null],
            ['metadata', null, InputOption::VALUE_NONE, 'publish metadata all contracts', null],
            ['text', null, InputOption::VALUE_NONE, 'publish text all contracts', null],
            ['category', null, InputOption::VALUE_OPTIONAL, 'publish contract based on contract type', null]
        ];
    }
}
