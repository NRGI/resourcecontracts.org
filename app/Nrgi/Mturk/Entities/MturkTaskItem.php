<?php

namespace App\Nrgi\Mturk\Entities;

use App\Nrgi\Services\Traits\DateTrait;
use Illuminate\Database\Eloquent\Model;
/**
 * Class MturkTaskItem
 * @property integer id
 * @property int      page_no
 * @property int      mturk_task_id
 * @method Illuminate\Database\Query\Builder where()
 * @package App\Nrgi\Mturk\Entities
 */
class MturkTaskItem extends Model
{
    use DateTrait;
    //
    protected $table = 'mturk_task_items';

     /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'task_id',
        'page_no',
        'text',
        'answer',
        'pdf_url'
    ];
}
