<?php namespace App\Nrgi\Services\CodeList;

use App\Nrgi\Repositories\CodeList\ContractType\ContractTypeRepositoryInterface;
use App\Nrgi\Repositories\CodeList\DocumentType\DocumentTypeRepositoryInterface;
use App\Nrgi\Repositories\CodeList\Resource\ResourceRepositoryInterface;
use App\Nrgi\Repositories\Contract\ContractRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ContractTypeService
 * @package App\Nrgi\Services\Metadata
 */
class CodeListService
{
    /**
     * @var ContractTypeRepositoryInterface
     */
    protected $contractType;

    /**
     * @var DocumentTypeRepositoryInterface
     */
    protected $documentType;

     /**
     * @var ResourceRepositoryInterface
     */
    protected $resource;

    /**
     * @var ContractRepositoryInterface
     */
    protected $contract;

     /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Codelist service constructor
     *
     * @param ContractTypeRepositoryInterface $contractType
     * @param DocumentTypeRepositoryInterface $documentType
     * @param ResourceRepositoryInterface $resource
     * @param ContractRepositoryInterface $contract
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContractTypeRepositoryInterface $contractType,
        DocumentTypeRepositoryInterface $documentType,
        ResourceRepositoryInterface $resource,
        ContractRepositoryInterface   $contract,
        LoggerInterface $logger
        )
    {
        $this->contractType = $contractType;
        $this->documentType = $documentType;
        $this->resource     = $resource;
        $this->contract     = $contract;
        $this->logger       = $logger;
    }

    /**
     * Get List of code list
     *  
     * @return array
     */
    public function all($type="contract_types")
    {
        if($type == 'resources'){

            return $this->resource->paginate();
        } elseif($type == 'document_types'){

            return $this->documentType->paginate();
        } else {

            return $this->contractType->paginate();
        }
    }

     /**
     * Get List of resource
     * 
     * @return array
     */
    public function getCodeList($type="contract_types", $lang)
    {
        if($type == 'resources'){

            return $this->resource->getResources($lang);
        } elseif($type == 'document_types'){

            return $this->documentType->getDocumentTypes($lang);
        } else {

            return $this->contractType->getContractTypes($lang);
        }
    }

    /**
     * Get codelist by type
     * 
     * @param  $type
     * @param  $id
     * 
     * @return Resources|Contract_types|Document_types
     */
    public function find($type="contract_types", $id)
    {
        if($type == 'resources'){

            return $this->resource->find($id);
        } elseif($type == 'document_types'){

            return $this->documentType->find($id);
        } else {

            return $this->contractType->find($id);
        }
    }

    /**
     * Stores codelist
     *
     * @param $data
     * 
     * @return Resources|Contract_types|Document_types|bool
     */
    public function store($data)
    {
        $type         = $data['type'];
        $data['slug'] = trim($data['en']);

        if($type =='resources'){

            return $this->resource->save($data);
        } elseif($type == 'document_types'){

            return $this->documentType->save($data);
        } else {

            return $this->contractType->save($data);
        }
    }

    /**
     * Update codelist
     *
     * @param $id
     * @param $data
     * 
     * @return bool
     */
    public function update($id,$data)
    {
        $type = $data['type'];

        if($type =='resources'){

            return $this->updateResource($id,$data);
        } elseif($type == 'document_types'){

            return $this->updateDocumentType($id,$data);
        } else {

            return $this->updateContractType($id,$data);
        }
    }

    /**
     * Update resource
     * 
     * @param $id
     * @param $formData
     * 
     * @return bool
     */
    public function updateResource($id,$formData){
        $resource = $this->find($formData['type'], $id);
       
        $data = array_except($formData, 'type');

        foreach ($data as $key => $value) {
            $resource[$key] = $value;
        }

        try {
            if ($resource->save()) {
               
                $this->logger->info('Resource successfully updated.', $formData);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

     /**
     * Update contract type
     * 
     * @param $id
     * @param $formData
     * 
     * @return bool
     */
    public function updateContractType($id,$formData){
        $contract_type = $this->find($formData['type'], $id);
       
        $data = array_except($formData, 'type');

        foreach ($data as $key => $value) {
            $contract_type[$key] = $value;
        }

        try {
            if ($contract_type->save()) {
               
                $this->logger->info('Contract type successfully updated.', $formData);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

     /**
     * Update document type
     * 
     * @param $id
     * @param $formData
     * 
     * @return bool
     */
    public function updateDocumentType($id,$formData){
        $document_type = $this->find($formData['type'], $id);
       
        $data = array_except($formData, 'type');

        foreach ($data as $key => $value) {
            $document_type[$key] = $value;
        }

        try {
            if ($document_type->save()) {
               
                $this->logger->info('Document type successfully updated.', $formData);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return false;
        }
    }

     /**
     * Checks if code list is previously used
     * 
     * @param $type
     * @param $id
     * 
     * @return bool
     */
    public function isNotUsed($type, $id) {

        $metadata = [ 
            'resources'      => 'resource',
            'contract_types' => 'type_of_contract',
            'document_types' => 'document_type'
        ];

        $slug = $this->find($type,$id)['slug'];

        return $this->contract->isCodeListNotUsed($metadata[$type], $slug);
    }

        /**
     * Update codelist
     *
     * @param $type
     * @param $id
     * 
     * @return bool
     */
    public function delete($type,$id)
    {
        if($type =='resources'){

            return $this->resource->delete($id);
        } elseif($type == 'document_types'){

            return $this->documentType->delete($id);
        } else {

            return $this->contractType->delete($id);
        }
    }
}
