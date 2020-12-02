<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mturk\Services\ActivityService;
use App\Nrgi\Services\Contract\Annotation\AnnotationService;
use App\Nrgi\Services\ElasticSearch\ElasticSearchService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command for Bulk index of data into elasticsearch
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
     * @var string
     */
    protected $contractCount;
    /**
     * @var ActivityService
     */
    protected $activity;
    /**
     * @var int
     */
    protected $remaining;

    /**
     * Create a new command instance.
     *
     * @param ElasticSearchService $elastic
     * @param AnnotationService    $annotations
     * @param Contract             $contract
     * @param ActivityService      $activity
     */
    public function __construct(
        ElasticSearchService $elastic,
        AnnotationService $annotations,
        Contract $contract,
        ActivityService $activity
    ) {
        parent::__construct();
        $this->elastic     = $elastic;
        $this->annotations = $annotations;
        $this->contract    = $contract;
        $this->activity    = $activity;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $category = $this->input->getOption('category');
        $skip = $this->input->getOption('skip');
        $take = $this->input->getOption('take');
        $id = $this->input->getOption('id');
        $batch = $this->input->getOption('batch');

        if (!$batch) {
            $contracts = $this->getContracts($category, $skip, $take, $id);
            $this->remaining = $contracts->count();
            $this->info("Total Contracts-".$this->remaining);

            $this->processContracts($contracts);
            return;
        }

        if (!$skip)
        {
            $skip = 0;
        }
        $this->remaining = Contract::count() - $skip;
        if ($take && $take < $this->remaining)
        {
            $this->remaining = $take;
        }
        $this->info("Total Contracts-".$this->remaining);

        if ($this->remaining == 0)
        {
            return;
        }

        while($this->remaining > 0)
        {
            if ($this->remaining < $batch)
            {
                $batch = $this->remaining;
            }

            $contracts = $this->getContracts($category, $skip, $batch, $id);
            $this->processContracts($contracts);

            $skip += $batch;
        }
    }

    public function processContracts($contracts)
    {
        if ($this->input->getOption('metadata')) {
            $this->publishAllMetadata($contracts);

            return;
        }

        if ($this->input->getOption('text')) {
            $this->publishAllText($contracts);

            return;
        }

        if ($this->input->getOption('annotation')) {
            $this->publishAnnotations($contracts);

            return;
        }

        $this->publishContracts($contracts);
    }

    /**
     * Publish all contract annotations
     *
     * @param $contracts
     */
    public function publishAnnotations($contracts)
    {
        foreach ($contracts as $contract) {
            $this->info(
                sprintf(
                    '%s) Contract id-%s %s',
                    $this->remaining,
                    $contract->id,
                    $this->publishAnnotation($contract)
                )
            );
            $this->status();
        }
    }

    /**
     * Publish all contract Metadata
     *
     * @param $contracts
     */
    public function publishAllMetadata($contracts)
    {
        foreach ($contracts as $contract) {
            $this->info(
                sprintf(
                    '%s) Contract id-%s %s',
                    $this->remaining,
                    $contract->id,
                    $this->publishMetadata($contract)
                )
            );
            $this->status();
        }

    }

    /**
     * Publish all contract Text
     *
     * @param $contracts
     */
    public function publishAllText($contracts)
    {
        foreach ($contracts as $contract) {
            $this->info(
                sprintf(
                    '%s) Contract id-%s %s',
                    $this->remaining,
                    $contract->id,
                    $this->publishText($contract)
                )
            );
            $this->status();
        }

    }

    /**
     * publish individual contract annotation
     *
     * @param Contract $contract
     *
     * @return bool
     */
    protected function publishAnnotation(Contract $contract)
    {
        if ($contract->activity['annotation'] == Contract::STATUS_PUBLISHED) {
            $this->elastic->deleteAnnotation($contract->id);
            $this->elastic->postAnnotation($contract->id);
        }

        return $contract->activity['annotation'];
    }

    /**
     * publish individual contract text only
     *
     * @param Contract $contract
     *
     * @return bool
     */
    protected function publishText(Contract $contract)
    {
        if ($contract->activity['text'] == Contract::STATUS_PUBLISHED) {
            $this->elastic->postText($contract->id, true, false);
        }

        return $contract->activity['text'];
    }

    /**
     * Publish individual contract metadata only
     *
     * @param Contract $contract
     *
     * @return string
     */
    protected function publishMetadata(Contract $contract)
    {
        if ($contract->activity['metadata'] == Contract::STATUS_PUBLISHED) {
            $this->elastic->postMetadata($contract->id);
        }

        return $contract->activity['metadata'];
    }

    /**
     * Publish text,metadata,annotation of all contracts
     *
     * @param $contracts
     */
    protected function publishContracts($contracts)
    {
        foreach ($contracts as $contract) {
            $this->info(
                sprintf(
                    '%s) Contract id-%s',
                    $this->remaining,
                    $contract->id
                )
            );

            $status = [
                $this->publishMetadata($contract),
                $this->publishText($contract),
                $this->publishAnnotation($contract),
            ];
            $this->table(['Metadata', 'Text', 'Annotation'], [$status]);
            $this->status();
        }
    }

    /**
     * Log status
     *
     * @param $index
     */
    protected function status()
    {
        $this->remaining -= 1;

        if ($this->remaining == 0) {
            $this->info("Process completed");
        }
    }

    protected function getContractsQuery($category, $skip, $take, $id)
    {
        $query = $this->contract;

        if ($category != 'all') {
            $from  = "contracts ";
            $from  .= ",json_array_elements(contracts.metadata->'category') cat";
            $query = $query->whereRaw("trim(both '\"' from cat::text) = '".$category."'");
            $query = $query->from(\DB::raw($from));
        }

        $query = $query->orderBy('id', 'ASC');

        if ($id) {
            $query = $query->where('id', $id);
        }

        if ($skip) {
            $query = $query->skip($skip);
        }

        if ($take != 'all') {
            $query = $query->take($take);
        }

        return $query;
    }

    /**
     * Get contracts based on options
     *
     * @return Collection
     */
    protected function getContracts($category, $skip, $take, $id)
    {
        $this->info(sprintf("Getting contracts category:%s skip:%s take:%s", $category, $skip, $take));

        $query = $this->getContractsQuery($category, $skip, $take, $id);
        $contracts = $query->get();

        $logs      = [];
        foreach ($contracts as &$contract) {
            try {
                $activity = $this->activity->getElementState($contract->id);
            } catch (\Exception $e) {
                $activity = ['metadata' => 'published', 'text' => 'published', 'annotation' => 'published'];
            }
            $contract->activity = $activity;
            $logs[]             = ['id' => $contract->id] + $activity;
        }
        $this->writeLog($logs);

        return $contracts;
    }

    /**
     * write contract publish log in file
     *
     * @param $contracts
     */
    protected function writeLog($contracts)
    {
        file_put_contents(storage_path('app/index.json'), json_encode($contracts));
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
            ['metadata', null, InputOption::VALUE_NONE, 'Publish only metadata', null],
            ['text', null, InputOption::VALUE_NONE, 'Publish only text', null],
            ['annotation', null, InputOption::VALUE_NONE, 'Publish only annotations', null],
            ['category', null, InputOption::VALUE_OPTIONAL, 'Publish contract based on contract type', 'all'],
            ['skip', null, InputOption::VALUE_OPTIONAL, 'Start contract from (Default is 0)', 0],
            ['take', null, InputOption::VALUE_OPTIONAL, 'Limit number of contracts', 'all'],
            ['id', null, InputOption::VALUE_OPTIONAL, 'Id of contract', null],
            ['batch', null, InputOption::VALUE_OPTIONAL, 'When not 0 retrieve contracts in batches of given size', null],
        ];
    }
}
