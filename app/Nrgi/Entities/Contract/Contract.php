<?php namespace App\Nrgi\Entities\Contract;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Contract
 * @package App\Nrgi\Entities\Contract
 */
class Contract extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_datetime';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'last_updated_datetime';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'contracts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['metadata', 'file', 'filehash', 'user_id'];

    /**
     * Convert json metadata to array
     * @param $metaData
     * @return mixed
     */
    public function getMetadataAttribute($metaData)
    {
        return json_decode($metaData);
    }

    /**
     * Convert Array metadata to json
     * @param $metaData
     * @return mixed
     */
    public function setMetadataAttribute($metaData)
    {
        $this->attributes['metadata'] = json_encode($metaData);
    }
}
