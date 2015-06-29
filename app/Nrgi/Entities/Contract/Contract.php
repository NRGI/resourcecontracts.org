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
    protected $fillable = ['metadata', 'file', 'filehash', 'user_id', 'textType', 'metadata_status', 'text_status'];

    /**
     * Contract Status
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';

    /**
     * Convert json metadata to array
     * @param $metaData
     * @return mixed
     */
    public function getMetadataAttribute($metaData)
    {
        $metaData           = json_decode($metaData);
        $metaData->file_url = getS3FileURL($this->file);

        return $metaData;
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function created_user()
    {
        return $this->belongsTo('App\Nrgi\Entities\User\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updated_user()
    {
        return $this->belongsTo('App\Nrgi\Entities\User\User', 'updated_by');
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

    /**
     * Get Contract Title
     */
    public function getTitleAttribute()
    {
        return $this->metadata->contract_name;
    }

    /**
     * Check if status is editable
     *
     * @param $status
     * @return bool
     */
    public function isEditableStatus($status)
    {
        if (in_array($status, [static::STATUS_COMPLETED, static::STATUS_PUBLISHED, static::STATUS_REJECTED])) {
            return true;
        }

        return false;
    }

    /**
     * Boot the Contact model
     * Attach event listener to add draft status when creating a contract
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();

        static::creating(
            function ($contract) {
                $contract->metadata_status = static::STATUS_DRAFT;
                $contract->text_status     = static::STATUS_DRAFT;

                return true;
            }
        );
    }
}
