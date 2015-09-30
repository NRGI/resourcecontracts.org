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
        $this->publishPdfText($metadata);
        $this->updateContractType($metadata);

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
     * Generate Open Contract ID
     *
     * @param $metadata
     * @return \App\Nrgi\Services\Contract\Identifier\ContractIdentifier
     */
    protected function generateOpenContractID(&$metadata)
    {
        if (!isset($metadata['open_contracting_id']) && isset($metadata['category'][0]) && isset($metadata['country']->code)) {
            $metadata['open_contracting_id'] = getContractIdentifier(
                $metadata['category'][0],
                $metadata['country']->code
            );
        }
    }


    /**
     * publish pdf text
     *
     * @param $metadata
     */
    protected function publishPdfText(&$metadata)
    {
        $metadata['show_pdf_text'] = 1;
    }

    /**
     * publish pdf text
     *
     * @param $metadata
     */
    protected function updateContractType(&$metadata)
    {
        $metadata['type_of_contract'] = $this->mapContractType($metadata['type_of_contract']);
    }

    /**
     * Update Government Entity
     *
     * @param $metadata
     * @return array
     */
    protected function updateGovernmentEntity(&$metadata)
    {
        if (!is_array($metadata['government_entity'])) {
            $governmentEntity     = isset($metadata['government_entity']) ? $metadata['government_entity'] : '';
            $governmentIdentifier = isset($metadata['government_identifier']) ? $metadata['government_identifier'] : '';

            $metadata['government_entity']   = [];
            $metadata['government_entity'][] = [
                "entity"     => $governmentEntity,
                "identifier" => $governmentIdentifier
            ];
        }

        if (isset($metadata['government_identifier'])) {
            unset($metadata['government_identifier']);
        }
    }

    /**
     * Map Resources
     *
     * @param array $resource
     * @return array
     */
    protected function mapResource(array $resource)
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
                $return[] = $res;
            }
        }

        return $return;
    }

    /**
     * Map Contract Type
     *
     * @param $type
     * @return string
     */
    protected function mapContractType($type)
    {
        $contractTypeList = $this->getContractTypeMappingList();

        if (array_key_exists($type, $contractTypeList)) {
            return $contractTypeList[$type];
        } else {
            return $type;
        }
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

    /**
     * Get ContractType Mapping list
     *
     * @return array
     */
    protected function getContractTypeMappingList()
    {
        return
            [
                "Amended and Restated Concession Agreement."      => "Concession Agreement",
                "Amended and Restated Land Concession Agreement " => "Concession Agreement",
                "Concession"                                      => "Concession Agreement",
                "Concession Agreement"                            => "Concession Agreement",
                "Contract to manage timber sale area"             => "Timber Sale Contract",
                "Contrat de concession forestiere"                => "Contrat de Concession Forestière",
                "Land Lease Agreement"                            => "Land Lease Agreement",
                "Land lease agreement"                            => "Land Lease Agreement",
                "Lease"                                           => "Land Lease Agreement",
                "Lease Agreement"                                 => "Land Lease Agreement",
                "Memorandum of Understanding"                     => "Memorandum of Understanding",
                "Memorandum of Understanding and Agreement"       => "Memorandum of Understanding",
                "Sub-lease"                                       => "Sub-lease",
                "Timber Sale Contract"                            => "Timber Sale Contract",
                "Timber Sales Contract"                           => "Timber Sale Contract",
            ];
    }

}