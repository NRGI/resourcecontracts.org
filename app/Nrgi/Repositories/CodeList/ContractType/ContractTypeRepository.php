<?php 
namespace App\Nrgi\Repositories\CodeList\ContractType;

use App\Nrgi\Entities\CodeList\ContractType;
use App\Nrgi\Repositories\CodeList\ContractType\ContractTypeRepositoryInterface;
use Illuminate\Database\DatabaseManager;

/**
 * Class CommentRepository
 */
class ContractTypeRepository implements ContractTypeRepositoryInterface
{
     /**
     * @var ContractType
     */
    protected $contractType;
    /**
     * @var DatabaseManager
     */
    protected $db;

    /**
     * @param ContractType $contractType
     * @param DatabaseManager $db
     */
    public function __construct(ContractType $contractType, DatabaseManager $db)
    {
        $this->contractType = $contractType;
        $this->db           = $db;   
    }

    /**
     * Save Contract type
     *
     * @param $contract_type
     * 
     * @return $contractType
     */
    public function save($contract_type)
    {
        return $this->contractType->create($contract_type);
    }

    /**
     * Delete Contract type
     *
     * @param $id
     * 
     * @return bool
     */
    public function delete($id)
    {
        return $this->contractType->destroy($id);
    }

    /**
     * Get all Contract types
     * 
     * @param 
     *
     * @return array
     */
    public function paginate($limit=25)
    {
        return $this->contractType->orderBy('en','ASC')->paginate($limit);
    }

    /**
     * Get all resources
     * 
     * @param $lang
     *
     * @return array
     */
    public function getContractTypes($lang)
    {
       return $this->contractType->orderBy('en','ASC')->pluck($lang,'slug')->all();
    }

    /**
     * Find contract_type by ID
     *
     * @param $id
     * 
     * @return ContractType
     */
    public function find($id)
    {
        return $this->contractType->find($id);
    }
}
