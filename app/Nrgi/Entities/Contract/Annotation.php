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
     * Convert json annotation to array
     * @param $metaData
     * @return mixed
     */
    public function getAnnotationAttribute($annotation)
    {
        return json_decode($annotation);
    }

    public function setAnnotationAttribute($annotation)
    {
        $this->attributes['annotation'] = json_encode($annotation);
    }
}
