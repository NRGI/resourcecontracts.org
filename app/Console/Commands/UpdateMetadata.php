<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Repositories\Contract\ContractRepository;
use App\Nrgi\Services\Contract\ContractService;
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
    private $contractService;

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
        $this->addIsContractSigned($metadata);
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
     * update disclosure mode
     *
     * @param $metadata
     */
    protected function updateDisclosureMode(&$metadata)
    {
        if ($metadata['disclosure_mode'] == "Corporate") {
            $metadata['disclosure_mode'] = "Company";
        }
    }

    /**
     * update disclosure mode
     *
     * @param $metadata
     */
    protected function addIsSupportingDocument(&$metadata)
    {
        unset($metadata['translated_from']);
        $metadata['is_supporting_document'] = $this->getIsSupportingContract();
    }

    /**
     * @return int
     */
    protected function getIsSupportingContract()
    {
        $exists = \DB::table('supporting_contracts')
                     ->whereSupportingContractId($this->contract->id)
                     ->count() > 0;
        if ($exists) {
            return "1";
        }

        return "0";
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
                "Amended and Restated Concession Agreement"                                   => "Concession Agreement",
                "Amended and Restated Concession Agreement."                                  => "Concession Agreement",
                "Amended and Restated Land Concession Agreement "                             => "Concession Agreement",
                "Amended and Restated Land Concession Agreement"                              => "Concession Agreement",
                "Concession"                                                                  => "Concession Agreement",
                "Concession Agreement"                                                        => "Concession Agreement",
                "Contract to manage timber sale area"                                         => "Timber Sale Contract",
                "Contrat de concession forestiere"                                            => "Contrat de Concession Forestière",
                "Contrat de concession forestière"                                            => "Contrat de Concession Forestière",
                "Land Lease Agreement"                                                        => "Land Lease Agreement",
                "Land lease agreement"                                                        => "Land Lease Agreement",
                "Lease"                                                                       => "Land Lease Agreement",
                "Lease Agreement"                                                             => "Land Lease Agreement",
                "Memorandum of Understanding"                                                 => "Memorandum of Understanding",
                "Memorandum of Understanding and Agreement"                                   => "Memorandum of Understanding",
                "Sub-lease"                                                                   => "Sub-lease",
                "Timber Sale Contract"                                                        => "Timber Sale Contract",
                "Timber Sales Contract"                                                       => "Timber Sale Contract",
                "Convention Particulière sur les Conditions de Cession et de Bail des Terres" => "Contrat de Concession Agricole",
            ];
    }

    /**
     * Update Additional Metadata. Add pages missing and annexes missing.
     *
     * @param $metadata
     */
    private function updateAdditionalMetadata(&$metadata)
    {

        if (!isset($metadata['pages_missing'])) {
            $metadata['pages_missing'] = "";
        }

        if (!isset($metadata['annexes_missing'])) {
            $metadata['annexes_missing'] = "";
        }


    }

    /*
     * Update company jurisdiction
     */
    private function updateCompanyJurisdiction(&$metadata)
    {
        $country     = trans('codelist/country');
        $country     = array_keys($country);
        $companys    = $metadata['company'];
        $companyData = [];
        foreach ($companys as $company) {
            $jurisdiction = $company->jurisdiction_of_incorporation;
            if (!empty($jurisdiction) && !in_array($jurisdiction, $country)) {
                $company->jurisdiction_of_incorporation = "";
            }
            $companyData[] = $company;
        }
        $metadata['company'] = $companyData;

    }

    /*
     * Update multiple contract type
     */
    private function multipleContractType(&$metadata)
    {
        $contractType = $metadata['type_of_contract'];
        if (!empty($contractType) && !is_array($contractType)) {
            $metadata['type_of_contract'] = [$contractType];
        } else {
            $metadata['type_of_contract'] = [];
        }

        return $metadata;
    }

    /**
     * Update document type as Contract if null
     * @param $metadata
     * @return mixed
     */
    private function updateDocumentType(&$metadata)
    {

        if (!isset($metadata['document_type']) || (isset($metadata['document_type']) && $metadata['document_type'] == '')) {
            $metadata['document_type'] = "Contract";
        }

        return $metadata;
    }

    /**
     * Updates the contract type
     * @param $metadata
     */
    private function updateAdditionalContractType(&$metadata)
    {
        $contractLists = [
            'Investment Promotion Agreements' => 'Investment Promotion Agreement',
            'Other//Autre'                    => 'Other',
            'Service Agreement'               => 'Service Contract'
        ];


        foreach ($contractLists as $oldValue => $contractList) {

            $contractTypeExists = in_array($oldValue, $metadata['type_of_contract']);
            if ($contractTypeExists) {
                $index                                = array_search($oldValue, $metadata['type_of_contract']);
                $metadata['type_of_contract'][$index] = $contractList;
            }
        }

    }

    /**
     * Update Document type
     * @param $metadata
     */
    private function updateAdditionalDocumentType(& $metadata)
    {
        $documentLists = [
            'Environmental Impact' => 'Environmental Impact Assessment',
            'Others'               => 'Other',
        ];

        foreach ($documentLists as $oldValue => $documentList) {
            if ($metadata['document_type'] == $oldValue) {
                $metadata['document_type'] = $documentList;
            }
        }

    }

    /**
     * Add is_contract_signed field in metadata
     * @param $metadata
     */
    public function addIsContractSigned(&$metadata)
    {
        $metadata['is_contract_signed']=1;
    }
}
