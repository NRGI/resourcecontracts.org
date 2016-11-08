<?php namespace App\Nrgi\Entities\Contract\Comment;

use App\Nrgi\Services\Traits\DateTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 * @package App\Nrgi\Entities\Contract\Comment
 */
class Comment extends Model
{
    use DateTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contract_id', 'user_id', 'message', 'type', 'action'];

    /**
     * Comment Types
     */
    const TYPE_METADATA = 'metadata';
    const TYPE_TEXT = 'text';
    const TYPE_ANNOTATION = 'annotation';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Nrgi\Entities\User\User');
    }
}
