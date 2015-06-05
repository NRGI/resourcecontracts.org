<?php namespace App\Nrgi\Entities\Contract\Pages;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pages
 * @package App\Nrgi\Entities\Contract\Pages
 */
class Pages extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_datetime';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'last_updated_datetime';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contract_id', 'page_no', 'text'];
}
