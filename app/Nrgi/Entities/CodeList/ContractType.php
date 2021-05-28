<?php 
namespace App\Nrgi\Entities\CodeList;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContractType
 */
class ContractType extends Model 
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_types';
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
                            'slug',
                            'en',
                            'fr',
                            'ar'
                            ];

}
