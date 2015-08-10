<?php namespace App\Nrgi\Entities\Contract;

use Illuminate\Database\Eloquent\Model;
use App\Nrgi\Scope\CountryScope;

/**
 * Class Contract
 * @package App\Nrgi\Entities\Contract
 */
class Annotation extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_annotations';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contract_id', 'annotation', 'url', 'user_id', 'document_page_no', 'page_id'];


    /**
     * Convert json annotation to array
     * @param $annotation
     * @return Array
     */
    public function getAnnotationAttribute($annotation)
    {
        return json_decode($annotation);
    }

    /**
     * @param $annotation
     */
    public function setAnnotationAttribute($annotation)
    {
        $this->attributes['annotation'] = json_encode($annotation);
    }

    /**
     * Establish one-to-many relationship with Page model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function page()
    {
        $this->belongsTo('App\Nrgi\Entities\Contract\Pages\Page', 'page_no', 'document_page_no');
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

