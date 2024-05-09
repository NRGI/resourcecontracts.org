<?php namespace App\Nrgi\Entities\ActivityLog;

use App\Nrgi\Services\Traits\DateTrait;
use Illuminate\Database\Eloquent\Model;
use App\Nrgi\Scope\CountryScope;

/**
 * Class ActivityLog
 * @property date created_at
 * @property string user
 * @package App\Nrgi\Entities\Contract
 */
class ActivityLog extends Model
{
    use DateTrait;

    /**
     * The fields that can be mass assigned
     * @var array
     */
    protected $fillable = ['contract_id', 'user_id', 'message', 'message_params'];
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * @param $params
     * @return Array
     */
    public function getMessageParamsAttribute($params)
    {
        if (is_null($params)) {
            return [];
        }

        return json_decode($params, true);
    }

    /**
     * @param $params
     */
    public function setMessageParamsAttribute($params)
    {
        $this->attributes['message_params'] = json_encode($params);
    }

    /**
     * Establish one-to-many relationship with User model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Nrgi\Entities\User\User');
    }

    /**
     * Establish one-to-many relationship with User model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo('App\Nrgi\Entities\Contract\Contract');
    }

    /**
     * Boot the ActivityLog model
     * Attach event listener query
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
    }

    public static function booted() {
        // static::addGlobalScope(new CountryScope);
    }
}
