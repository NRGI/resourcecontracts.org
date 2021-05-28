<?php namespace App\Nrgi\Repositories\CodeList\Resource;

use App\Nrgi\Entities\CodeList\Resource;
use App\Nrgi\Repositories\CodeList\Resource\ResourceRepositoryInterface;
use Illuminate\Database\DatabaseManager;

/**
 * ResourceRepository class
 */
class ResourceRepository implements ResourceRepositoryInterface
{
     /**
     * @var Resource
     */
    protected $resource;
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param Resource $resource
     * @param DatabaseManager $db
     */
    public function __construct(Resource $resource, DatabaseManager $db)
    {
        $this->resource = $resource;
        $this->db       = $db;   
    }

    /**
     * Save resource
     *
     * @param $resource
     * 
     * @return Resource
     */
    public function save($resource)
    {
        return $this->resource->create($resource);
    }

    /**
     * Delete resource
     *
     * @param $id
     * 
     * @return bool
     */
    public function delete($id)
    {
       return $this->resource->destroy($id);
    }

    /**
     * Get all resources
     *
     * @param $limit
     * 
     * @return array
     */
    public function paginate($limit=25)
    {
       return $this->resource->orderBy('en','ASC')->paginate($limit);
    }

    /**
     * Get all resources
     * 
     * @param $lang
     *
     * @return array
     */
    public function getResources($lang)
    {
       return $this->resource->orderBy('en','ASC')->pluck($lang,'slug')->all();
    }

    /**
     * Find resource by ID
     *
     * @param $id
     * 
     * @return Resource
     */
    public function find($id)
    {
        return $this->resource->find($id);
    }
}
