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
         if (!isset($metadata['open_contracting_id']) && isset($metadata['category'][0]) && isset($metadata['country']->code)) {
             $metadata['open_contracting_id'] = getContractIdentifier($metadata['category'][0], $metadata['country']->code);
         }

         if (!is_array($metadata['government_entity'])) {
             $governmentEntity     = isset($metadata['government_entity']) ? $metadata['government_entity'] : '';
             $governmentIdentifier = isset($metadata['government_identifier']) ? $metadata['government_identifier'] : '';

             if (isset($metadata['government_identifier'])) {
                 unset($metadata['government_identifier']);
             }

             $metadata['government_entity']   = [];
             $metadata['government_entity'][] = [
                 "entity"     => $governmentEntity,
                 "identifier" => $governmentIdentifier
             ];
         }

         if (isset($metadata['government_identifier'])) {
             unset($metadata['government_identifier']);
         }

        $metadata['resource'] = $this->mapResource($metadata['resource']);

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

    /**
     * Map Resources
     *
     * @param array $resource
     * @return array
     */
    protected function mapResource($resource)
    {
        $resourceMapList = $this->getResourceMappingList();

        if (empty($resource)) {
            return [];
        }

        $return = [];

        foreach ($resource as $res) {
            if (array_key_exists($res, $resourceMapList)) {
                $return[] = $resourceMapList[$res];
            } else {
                if ($res == 'null' || is_null($res)) {
                    $return[] = '';
                } else {
                    $return[] = trim($res);
                }
            }
        }

        return $return;
    }

    /**
     * Get Resource Mapping list
     *
     * @return array
     */
    protected function getResourceMappingList()
    {
        return [
            'Acacia'                        => 'Acacia',
            'Agroindustry'                  => 'Agroindustry',
            'Biofuels'                      => 'Biofuels',
            'Castor oil (Ricinus communis)' => 'Castor oil (Ricinus communis)',
            'Cereal crops'                  => 'Cereal crops',
            'Coffee'                        => 'Coffee',
            'Cotton'                        => 'Cotton',
            'Eucalyptus'                    => 'Eucalyptus',
            'Grain legumes (Pulses)'        => 'Grain legumes (Pulses)',
            'Groundnuts'                    => 'Groundnuts',
            'Jatropha curcas'               => 'Jatropha curcas',
            'Maize (Corn)'                  => 'Maize (Corn)',
            'Medicinal plants'              => 'Medicinal plants',
            'Megafolia-paulownia'           => 'Timber (Wood)',
            'Oil crops'                     => 'Oil crops',
            'Oil palm'                      => 'Oil palm or palm oils',
            'Oilseeds'                      => 'Oilseeds',
            'Other crops'                   => 'Other crops',
            'Palm oil'                      => 'Oil palm or palm oils',
            'Palm oils'                     => 'Oil palm or palm oils',
            'Pongamia'                      => 'Biofuels ',
            'Rice'                          => 'Rice',
            'Rubber'                        => 'Rubber ',
            'Sesame'                        => 'Sesame',
            'Soybeans (Soya beans)'         => 'Soybeans (Soya beans)',
            'Sugarcane'                     => 'Sugarcane',
            'Tea'                           => 'Tea',
            'Teak (Tectona grandis)'        => 'Teak (Tectona grandis)',
            'Timber'                        => 'Timber (Wood)',
            'Timber (wood)'                 => 'Timber (Wood)',
            'Value-added crops'             => 'Value-added crops'
        ];
    }

}