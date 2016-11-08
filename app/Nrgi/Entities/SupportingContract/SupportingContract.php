<?php namespace App\Nrgi\Entities\SupportingContract;

use Illuminate\Database\Eloquent\Model;

/**
 * Class SupportingDocument
 * @package App\Nrgi\Entities\SupportingDocument
 */
class SupportingContract extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'supporting_contracts';
    /**
     * @var array
     */
    protected $fillable = ['contract_id', "supporting_contract_id"];

    /**
     * @var bool
     */
    public $timestamps = false;

}
