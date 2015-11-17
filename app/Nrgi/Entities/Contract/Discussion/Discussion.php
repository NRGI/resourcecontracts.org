<?php namespace App\Nrgi\Entities\Contract\Discussion;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Discussion
 * @package App\Nrgi\Entities\Contract\Discussion
 */
class Discussion extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_discussions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contract_id', 'user_id', 'message', 'type', 'key', 'status'];

    /**
     * Discussion Types
     */
    const TYPE_METADATA = 'metadata';
    /**
     *
     */
    const TYPE_ANNOTATION = 'annotation';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Nrgi\Entities\User\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo('App\Nrgi\Entities\Contract\Contract');
    }
}
