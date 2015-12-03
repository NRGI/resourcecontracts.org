<?php namespace App\Nrgi\Entities\Contract\Pages;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pages
 * @property int     id
 * @property string  pdf_url
 * @property string  text
 * @property int     contract_id
 * @property int     page_no
 * @package App\Nrgi\Entities\Contract\Pages
 */
class Pages extends Model
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
    protected $fillable = ['contract_id', 'page_no', 'text'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function annotations()
    {
        return $this->hasMany('App\Nrgi\Entities\Contract\Annotation', 'document_page_no');
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
