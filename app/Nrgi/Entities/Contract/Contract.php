<?php namespace App\Nrgi\Entities\Contract;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

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
    protected $fillable = ['metadata', 'file', 'filehash', 'user_id', 'textType'];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pages()
    {
        return $this->hasMany('App\Nrgi\Entities\Contract\Pages\Pages');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function annotations()
    {
        return $this->hasMany('App\Nrgi\Entities\Contract\Annotation');
    }


    /**
     * Get Text Type by Key
     * @param null $key
     * @return mixed
     */
    public function getTextType($key = null)
    {
        if (is_null($key)) {
            $key = $this->textType;
        }

        $type = config('metadata.text_type');

        if (array_key_exists($key, $type)) {
            return (object) $type[$key];
        }

        throw new InvalidArgumentException;
    }
}
