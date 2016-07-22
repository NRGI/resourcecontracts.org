<?php namespace App\Nrgi\Entities\Contract\Annotation;

use App\Nrgi\Services\Traits\DateTrait;
use Illuminate\Database\Eloquent\Model;
use App\Nrgi\Scope\CountryScope;

/**
 * Class Contract
 * @property int id
 * @property int status
 * @package App\Nrgi\Entities\Contract\Annotation
 */
class Annotation extends Model
{
    use DateTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_annotations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['category', 'contract_id', 'status', 'text'];

    /**
     * annotation status published
     */
    const DRAFT = 'draft';

    /**
     *  annotation status draft
     */
    const COMPLETED = 'completed';

    /**
     * annotation status published
     */
    const PUBLISHED = 'published';

    /**
     *  annotation status draft
     */
    const REJECTED = 'rejected';
    /*
     * annotation status unpublished
     */
    const UNPUBLISH = 'unpublished';

    /**
     * Establish one-to-many relationship with User model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contract()
    {
        return $this->belongsTo('App\Nrgi\Entities\Contract\Contract');
    }

    /**
     * Establish one-to-many relationship with Annotation Page model
     *
     */
    public function child()
    {
        return $this->hasMany('App\Nrgi\Entities\Contract\Annotation\Page\Page','annotation_id', 'id');
    }

    /**
     * Boot the Annotation model
     * Attach event listener
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(new CountryScope);
    }
}

