<?php namespace App\Nrgi\Entities\Contract\Pages;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pages
 * @package App\Nrgi\Entities\Contract\Pages
 */
class Pages extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contract_id', 'page_no', 'text'];
}
