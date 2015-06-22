<?php namespace App\Nrgi\Entities\Contract;

use Illuminate\Database\Eloquent\Model;

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
}

