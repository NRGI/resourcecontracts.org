<?php namespace App\Nrgi\Entities\ExternalApi;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ExternalApi
 * @package App\Nrgi\Entities\ExternalApi
 */
class ExternalApi extends Model
{
    /**
     * @var string
     */
    protected $table = 'external_apis';

    /**
     *
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'site',
        'url',
        'last_index_date',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_index_date',
    ];

    /**
     * Get Slug
     *
     * @return string
     */
    public function getSlugAttribute()
    {
        return camel_case($this->site);
    }

    /**
     * Update index date.
     *
     * @param string $value
     *
     * @return bool
     */
    public function updateIndexDate($value = '')
    {
        $this->last_index_date = is_null($value) ? $value : date('Y-m-d H:i:s');
        $this->save();

        return $this;
    }

}
