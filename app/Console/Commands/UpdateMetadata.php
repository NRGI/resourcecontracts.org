<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepository;
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
     * Create a new command instance.
     *
     * @param ContractRepository $contract
     */
    public function __construct(ContractRepository $contract)
    {
        parent::__construct();
        $this->contract = $contract;
    }

    /**
     * Execute the console command.
     *
     */
    public function fire()
    {
        $contract_id = $this->input->getOption('id');

        if (is_null($contract_id)) {
            $contracts = $this->contract->getList();

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

        $this->info(sprintf('Contract ID %s : UPDATED', $contract->id));
    }

    /**
     * Apply rules to metadata update
     *
     * @param array $metadata
     * @return array
     */
    protected function applyRules(array $metadata)
    {
        if (!isset($metadata['open_contracting_id'])) {
            $metadata['open_contracting_id'] = getContractIdentifier();
        }

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
