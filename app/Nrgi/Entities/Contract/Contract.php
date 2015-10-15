<?php namespace App\Nrgi\Entities\Contract;

use Illuminate\Database\Eloquent\Collection;
use App\Nrgi\Scope\CountryScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Class Contract
 * @property Collection                                                                   tasks
 * @property Collection                                                                   pages
 * @property int                                                                          id
 * @property int                                                                          mturk_status
 * @property array                                                                        metadata
 * @property int                                                                          textType
 * @property string                                                                       title
 * @property Collection                                                                   annotations
 * @property string                                                                       file
 * @property string                                                                       filehash
 * @property int                                                                          pdf_process_status
 * @property string                                                                       word_file
 * @property int                                                                          updated_by
 * @property string                                                                       metadata_status
 * @property string                                                                       text_status
 * @property int                                                                          created_user
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
    const STATUS_DRAFT     = 'draft';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED  = 'rejected';

    /**
     * Contract Processing
     */
    const PROCESSING_PIPELINE = 0;
    const PROCESSING_RUNNING  = 1;
    const PROCESSING_COMPLETE = 2;
    const PROCESSING_FAILED   = 3;

    /**
     * MTurk Status
     */
    const MTURK_SENT     = 1;
    const MTURK_COMPLETE = 2;
    const SHOW_PDF_TEXT  = 1;

    /**
     * Convert json metadata to array
     *
     * @param $metaData
     * @return array
     */
    public function getMetadataAttribute($metaData)
    {
        $metaData            = json_decode($metaData);
        $metaData->amla_url  = $this->getAmlaUrl($metaData->country->code);
        $metaData->file_url  = $this->file_url;
        $metaData->word_file = $this->word_file;

        return $this->makeNullField($metaData);
    }

    /**
     * Get pdf url
     *
     * @return string
     */
    public function getFileUrlAttribute()
    {
        return getS3FileURL($this->id . '/' . $this->file);
    }

    /**
     * Get Contract Slug
     *
     * @return string
     */
    public function getSlugAttribute()
    {
        return str_limit(str_slug($this->metadata->contract_name, '-'), 150);
    }

    /**
     * Get word file url
     *
     * @return string
     */
    public function getWordFileAttribute()
    {
        if ($this->pdf_process_status == static::PROCESSING_COMPLETE) {
            list($filename, $ext) = explode('.', $this->file);
            $wordFileName = $filename . '.txt';

            return getS3FileURL($this->id . '/' . $wordFileName);
        }

        return '';
    }

    /**
     * Convert Array metadata to json
     * @param $metaData
     * @return void
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function SupportingContract()
    {
        return $this->belongsToMany(
            'App\Nrgi\Entities\SupportingContract\SupportingContract',
            'supporting_contract',
            'contract_id'
        );
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks()
    {
        return $this->hasMany('App\Nrgi\Mturk\Entities\Task');
    }

    /**
     * Get Text Type by Key
     *
     * @param null $key
     * @return object
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
     *
     * @return string
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
     * Make metadata value null if value is empty
     *
     * @param $metadata
     * @return object
     */
    public function makeNullField($metadata)
    {
        $nullable_fields = ['signature_date', 'date_retrieval', 'company_founding_date'];

        foreach ($metadata as $key => &$value) {
            if (is_object($value)) {
                $value = (object) $this->makeNullField((array) $value);
            }

            if (is_array($value)) {
                $value = $this->makeNullField($value);
            }

            if (in_array($key, $nullable_fields) && $value == '') {
                $value = null;
            }
        }

        return $metadata;
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
        static::addGlobalScope(new CountryScope);
        static::creating(
            function ($contract) {
                $contract->metadata_status    = static::STATUS_DRAFT;
                $contract->text_status        = null;
                $contract->pdf_process_status = static::PROCESSING_PIPELINE;
                $contract->mturk_status       = null;

                return true;
            }
        );
    }

    /**
     * Put AMLA url if the country exist in amla config file.
     *
     * @param $code
     * @return string
     */
    public function getAmlaUrl($code)
    {
        return isset(config('amla')[$code]) ? config('amla')[$code] : '';
    }

    /**
     * S3 filename.
     *
     * @return string
     */
    public function getS3PdfName()
    {
        return sprintf("%s-%s.pdf", $this->id, $this->Slug);
    }

    /**
     * Sync of supporting contracts
     *
     * @param $contract_id
     * @return bool
     */
    public function syncSupportingContracts($contract_id)
    {
        DB::table('supporting_contracts')->where('contract_id', $this->id)->delete();

        if (empty($contract_id)) {
            return true;
        }

        if (!is_array($contract_id)) {
            $contract_id = [$contract_id];
        }

        $insert = [];

        foreach ($contract_id as $id) {
            $insert[] = [
                'contract_id' => $this->id,
                'supporting_contract_id' => $id
            ];
        }

        return DB::table('supporting_contracts')->insert($insert);
    }

    /**
     * Get the list of supporting Contract
     *
     * @return array
     */
    public function getSupportingContract()
    {
        return DB::table('supporting_contracts')->where('contract_id', $this->id)->lists('supporting_contract_id');
    }

}
