<?php namespace Nrgi\Entities\Contract;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Contract
 * @package Nrgi\Entities\Contract
 */
class Annotation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_annotations';
}
