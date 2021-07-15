<?php 
namespace App\Nrgi\Repositories\CodeList\DocumentType;

use App\Nrgi\Entities\CodeList\DocumentType;
use App\Nrgi\Repositories\CodeList\DocumentType\DocumentTypeRepositoryInterface;
use Illuminate\Database\DatabaseManager;

/**
 * Class DocumentTypeRepository
 */
class DocumentTypeRepository implements DocumentTypeRepositoryInterface
{
     /**
     * @var DocumentType
     */
    protected $documentType;

    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param DocumentType $documentType
     * @param DatabaseManager $db
     */
    public function __construct(DocumentType $documentType, DatabaseManager $db)
    {
        $this->documentType = $documentType;
        $this->db           = $db;   
    }


    /**
     * Save document type
     *
     * @param $document_type
     * 
     * @return DocumentType
     */
    public function save($document_type){
        return $this->documentType->create($document_type);

    }

    /**
     * Delete document type
     *
     * @param $id
     * 
     * @return bool
     */
    public function delete($id)
    {
        return $this->documentType->destroy($id);
    }

    /**
     * Get all document types
     * 
     * @param $limit
     *
     * @return array
     */
    public function paginate($limit=25)
    {
        return $this->documentType->orderBy('en','ASC')->paginate($limit);
    }

    /**
     * Get all document types
     * 
     * @param $lang
     *
     * @return array
     */
    public function getDocumentTypes($lang)
    {
       return $this->documentType->orderBy('en','ASC')->pluck($lang,'slug')->all();
    }

    /**
     * Find contract_type by ID
     *
     * @param $id
     * 
     * @return DocumentType
     */
    public function find($id)
    {
        return $this->documentType->find($id);
    }
}
