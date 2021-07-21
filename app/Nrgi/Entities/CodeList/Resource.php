<?php 
namespace App\Nrgi\Entities\CodeList;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Resource
 */
class Resource extends Model 
{
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
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'resources';

}
