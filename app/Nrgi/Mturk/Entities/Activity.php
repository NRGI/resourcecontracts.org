<?php namespace App\Nrgi\Mturk\Entities;

use App\Nrgi\Services\Traits\DateTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Activity
 * @package App\Nrgi\Mturk\Entities
 */
class Activity extends Model
{
    use DateTrait;
    /**
     * The fields that can be mass assigned
     * @var array
     */
    protected $fillable = ['page_no', 'contract_id', 'user_id', 'message', 'message_params'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mturk_activities';

    /**
     * Get Message Params
     *
     * @param $params
     * @return array
     */
    public function getMessageParamsAttribute($params)
    {
        if (is_null($params)) {
            return [];
        }

        return json_decode($params, true);
    }

    /**
     * Set Message Params
     *
     * @param $params
     * @return void
     */
    public function setMessageParamsAttribute($params)
    {
        $this->attributes['message_params'] = json_encode($params);
    }

    /**
     * Establish one-to-many relationship with User model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Nrgi\Entities\User\User');
    }

    /**
     * Establish one-to-many relationship with Task model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo('App\Nrgi\Mturk\Entities\Task');
    }

    /**
     * Establish one-to-many relationship with Task model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo('App\Nrgi\Entities\Contract\Contract');
    }
}
