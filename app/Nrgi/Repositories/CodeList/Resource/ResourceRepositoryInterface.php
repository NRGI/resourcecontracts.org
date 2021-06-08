<?php namespace App\Nrgi\Repositories\CodeList\Resource;

/**
 * Class CommentRepositoryInterface
 * @package App\Nrgi\Repositories\Contract\Comment
 */
interface ResourceRepositoryInterface
{
    /**
     * Save CodeList
     *
     * @param $codelist
     * 
     * @return Resource
     */
    public function save($codelist);


    /**
     * Delete CodeList
     *
     * @param $id
     * 
     * @return bool
     */
    public function delete($id);

    /**
     * Get all code list
     * 
     * @return array
     */
    public function paginate($limit);

      /**
     * Get all resources
     *
     * @return array
     */
    public function getResources($lang);
   
    /**
     * Find resource by ID
     *
     * @param $id
     * 
     * @return Resource
     */
    public function find($id);
}
