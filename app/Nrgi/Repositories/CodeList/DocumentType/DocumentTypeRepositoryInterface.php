<?php namespace App\Nrgi\Repositories\CodeList\DocumentType;

/**
 * DocumentType interface
 */
interface DocumentTypeRepositoryInterface
{
    /**
     * Save document type
     *
     * @param $codelist
     * 
     * @return DocumentType
     */
    public function save($codelist);


    /**
     * Delete document type
     *
     * @param $id
     * 
     * @return bool
     */
    public function delete($id);

    /**
     * Get all code list
     * 
     * @param $limit
     * 
     * @return array
     */
    public function paginate($limit);

     /**
     * Get all document types
     *
     * @param $lang
     * 
     * @return array
     */
    public function getDocumentTypes($lang);


    /**
     * Find document type by ID
     *
     * @param $id
     * 
     * @return DocumentType
     */
    public function find($id);
}
