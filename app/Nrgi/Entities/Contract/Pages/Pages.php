<?php namespace App\Nrgi\Entities\Contract\Pages;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Pages
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
        return $this->hasMany('App\Nrgi\Entities\Contract\Annotation', 'page_id');
    }

    /**
     * Get Pdf file url
     * @return string
     */
    public function getPdfUrlAttribute()
    {
        return getPdfUrl($this->contract_id, $this->page_no);
    }

}
