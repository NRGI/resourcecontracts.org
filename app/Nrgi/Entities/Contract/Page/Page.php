<?php namespace App\Nrgi\Entities\Contract\Page;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pages
 * @property int     id
 * @property string  pdf_url
 * @property string  text
 * @property string  text_en
 * @property string  text_es
 * @property string  text_fr
 * @property bool    is_translation_valid
 * @property string  translation_status
 * @property int     contract_id
 * @property int     page_no
 * @package App\Nrgi\Entities\Contract\Pages
 */
class Page extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contract_pages';

    protected $appends = ['pdf_url'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contract_id', 'page_no', 'text', 'text_en', 'text_es', 'text_fr', 'is_translation_valid', 'translation_status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function annotations()
    {
        return $this->hasMany('App\Nrgi\Entities\Contract\Annotation\Page\Page', 'page_no');
    }

    /**
     * Get Pdf file url
     * @return string
     */
    public function getPdfUrlAttribute()
    {
        return getS3FileURL(sprintf('%s/%s.pdf', $this->contract_id, $this->page_no));
    }
}
