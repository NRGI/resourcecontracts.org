<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepository;
use App\Nrgi\Services\Contract\ContractService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class UpdateMetadata
 * @package App\Console\Commands
 */
class UpdateMetadata extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:updatemetadata';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Metadata.';
    /**
     * @var ContractRepository
     */
    protected $contract;
    /**
     * @var ContractService
     */
    protected $contractService;

    /**
     * Create a new command instance.
     *
     * @param ContractRepository $contract
     * @param ContractService    $contractService
     */
    public function __construct(ContractRepository $contract, ContractService $contractService)
    {
        parent::__construct();
        $this->contract        = $contract;
        $this->contractService = $contractService;
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $contract_id = $this->input->getOption('id');

        if (is_null($contract_id)) {
            $contracts = $this->contract->getList();
            $contracts = $contracts->sortBy('id');
            foreach ($contracts as $contract) {
                $this->updateMetadata($contract);
            }
        } else {
            $contract = $this->contract->findContract($contract_id);
            $this->updateMetadata($contract);
        }
    }

    /**
     * Update Contract Metadata
     *
     * @param Contract $contract
     */
    public function updateMetadata(Contract $contract)
    {
        $default  = config('metadata.schema.metadata');
        $metadata = (array) $contract->metadata;
        $metadata = array_merge($default, $metadata);
        unset($metadata['amla_url'], $metadata['file_url'], $metadata['word_file']);
        $contract->metadata = $this->applyRules($metadata);
        $contract->save();
    }

    /**
     * Update Signature Date
     *
     * @param $metadata
     */
    public function updateSignatureDate(&$metadata)
    {
        if (!empty($metadata['signature_date'])) {
            $date                       = Carbon::createFromFormat('Y-m-d', $metadata['signature_date']);
            $metadata['signature_date'] = $date->format('Y-m-d');
        }
    }

    /**
     * Apply rules to metadata update
     *
     * @param array $metadata
     *
     * @return array
     */
    protected function applyRules(array $metadata)
    {
        $this->updateSignatureDate($metadata);

        return $metadata;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['id', null, InputOption::VALUE_OPTIONAL, 'Contract ID.', null],
        ];
    }
}
