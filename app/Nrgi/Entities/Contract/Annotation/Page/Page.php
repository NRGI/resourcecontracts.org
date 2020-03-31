<?php namespace App\Nrgi\Entities\Contract\Annotation\Page;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Page
 * @package App\Nrgi\Entities\Contract\Annotation\Page
 */
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
    protected $fillable = [
        'annotation_id',
        'user_id',
        'page_no',
        'annotation',
        'article_reference',
        'article_reference_trans',
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ['article_reference_trans' => 'json'];

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
     * Get Article Reference
     *
     * @param $article_reference
     *
     * @return array
     */
    public function getArticleReferenceTransAttribute($article_reference)
    {
        $lang              = app('App\Nrgi\Services\Language\LanguageService');
        $data              = [];
        $article_reference = json_decode($article_reference);

        foreach ($lang->translation_lang() as $l) {
            if ($l['code'] != $lang->defaultLang()) {
                $code=$l['code'];
                $data[$l['code']] = isset($article_reference->$code) ? $article_reference->$code : $this->article_reference;
            }
        }

        return $data;
    }

    /**
     * get annotation json
     *
     */
    public function getAnnotationAttribute($annotation)
    {
        return json_decode($annotation);
    }

    /**
     * Set Translation language
     *
     * @param $lang
     */
    public function setLang($lang)
    {
        if (isset($this->article_reference_trans[$lang])) {
            $this->article_reference = $this->article_reference_trans[$lang];
        } else {
            $this->article_reference = $this->getOriginal('article_reference');
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('App\Nrgi\Entities\Contract\Annotation\Annotation', 'annotation_id', 'id');
    }

}
