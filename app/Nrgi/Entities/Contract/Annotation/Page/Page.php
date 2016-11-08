<?php namespace App\Nrgi\Entities\Contract\Annotation\Page;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * @var string
     */
    protected $table = 'contract_annotation_pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['annotation_id', 'user_id', 'page_no', 'annotation', 'article_reference'];

    /**
     * Convert json annotation to array
     *
     * @param $annotation
     * @return array
     */
    public function getAnnotationAttribute($annotation)
    {
        return json_decode($annotation);
    }

    /**
     * Save annotation as json
     *
     * @param $annotation
     */
    public function setAnnotationAttribute($annotation)
    {
        $this->attributes['annotation'] = json_encode($annotation);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('App\Nrgi\Entities\Contract\Annotation\Annotation', 'annotation_id', 'id');
    }

}
