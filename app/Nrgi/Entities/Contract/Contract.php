<?php namespace App\Nrgi\Entities\Contract;

use Illuminate\Database\Eloquent\Collection;
use App\Nrgi\Scope\CountryScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Class Contract
 * @property Collection tasks
 * @property Collection pages
 * @property int        id
 * @property int        mturk_status
 * @property array      metadata
 * @property int        textType
 * @property string     title
 * @property Collection annotations
 * @property string     file
 * @property string     filehash
 * @property int        pdf_process_status
 * @property string     word_file
 * @property int        updated_by
 * @property string     metadata_status
 * @property string     text_status
 * @property int        created_user
 * @property string     file_url
 * @property string     slug
 * @property int        user_id
 * @property string     updated_user
 * @property string     created_datetime
 * @property string     last_updated_datetime
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
     * Contract update on draft Status
     */
    const STATUS_DRAFT = 'draft';
    /**
     * Contract update on completed Status
     */
    const STATUS_COMPLETED = 'completed';
    /**
     * Contract published Status
     */
    const STATUS_PUBLISHED = 'published';
    /**
     * Contract unpublished Status
     */
    const STATUS_UNPUBLISHED = 'unpublished';
    /**
     * Contract update on rejected Status
     */
    const STATUS_REJECTED = 'rejected';
    /**
     * Contract Processing
     */
    const PROCESSING_PIPELINE = 0;
    /**
     * Contract process running
     */
    const PROCESSING_RUNNING = 1;
    /**
     * Contract process completed
     */
    const PROCESSING_COMPLETE = 2;
    /**
     * Contract process failed
     */
    const PROCESSING_FAILED = 3;
    /**
     * OCR Text send to MTurk
     */
    const MTURK_SENT = 1;
    /**
     * Mturk Task complete
     */
    const MTURK_COMPLETE = 2;
    /**
     * Show pdf text on frontend
     */
    const SHOW_PDF_TEXT = 1;
    /**
     * Pdf Text acceptable
     */
    const ACCEPTABLE = 1;
    /**
     * Pdf Text needs editing
     */
    const NEEDS_EDITING = 2;
    /**
     * Pdf text needs full transcription
     */
    const NEEDS_FULL_TRANSCRIPTION = 3;
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
    protected $fillable = [
        'metadata',
        'metadata_trans',
        'file',
        'filehash',
        'user_id',
        'textType',
        'metadata_status',
        'text_status',
    ];
    /**
     * @var array
     */
    protected $casts = [
        'metadata_trans' => 'object',
    ];

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
     * Set Translation language
     *
     * @param $lang
     */
    public function setLang($lang)
    {
        if (isset($this->metadata_trans->$lang)) {
            $metadata_en    = json_decode($this->getOriginal('metadata'), true);
            $metadata_trans = (array) $this->metadata_trans->$lang;
            $metadata       = array_replace_recursive($metadata_en, $metadata_trans);

            foreach ($metadata['company'] as $key => $company) {
                $metadata['company'][$key] = array_replace_recursive(
                    (array) $metadata_en['company'][$key],
                    (array) $company
                );
            }
            $this->metadata = $metadata;
        } else {
            $this->metadata = json_decode($this->getOriginal('metadata'), true);
        }
    }

    /**
     * Determine if metadata has the translation
     *
     * @param $locale
     *
     * @return bool
     */
    public function hasTranslation($locale)
    {
        if (config('lang.default') == $locale) {
            return true;
        }

        $metadata = json_decode($this->getOriginal('metadata_trans'), true);
        if (isset($metadata[$locale])) {
            return true;
        }

        return false;
    }

    /**
     * Convert json metadata to array
     *
     * @param $metaData
     *
     * @return array
     */
    public function getMetadataAttribute($metaData)
    {
        $metaData            = json_decode($metaData);
        $metaData->amla_url  = $this->getAmlaUrl($metaData->country->code, $metaData->resource);
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
        $path = $this->id.'/'.$this->file;

        if ($this->pdf_process_status == self::PROCESSING_PIPELINE || $this->pdf_process_status == self::PROCESSING_RUNNING) {
            $path = $this->file;
        }

        return getS3FileURL($path);
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
            $filename     = explode('.', $this->file);
            $filename     = $filename[0];
            $wordFileName = $filename.'.txt';

            return getS3FileURL($this->id.'/'.$wordFileName);
        }

        return '';
    }

    /**
     * Convert Array metadata to json
     *
     * @param $metaData
     *
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
        return $this->hasMany('App\Nrgi\Entities\Contract\Page\Page');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function annotations()
    {
        return $this->hasMany('App\Nrgi\Entities\Contract\Annotation\Annotation');
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
     *
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
     *
     * @return bool
     */
    public function isEditableStatus($status)
    {
        if (in_array(
            $status,
            [
                static::STATUS_DRAFT,
                static::STATUS_COMPLETED,
                static::STATUS_PUBLISHED,
                static::STATUS_REJECTED,
                static::STATUS_UNPUBLISHED,
            ]
        )) {
            return true;
        }

        return false;
    }

    /**
     * Make metadata value null if value is empty
     *
     * @param $metadata
     *
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
     * Put AMLA url if the country exist in amla config file.
     *
     * @param $code
     *
     * @return string
     */
    public function getAmlaUrl($code, $resource)
    {
        $except_resource = ["Hydrocarbons", "Oil", "Gas"];
        $filter          = array_intersect($resource, $except_resource);

        return (isset(config('amla')[$code]) && (empty($filter))) ? config('amla')[$code] : '';
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
     *
     * @return bool
     */
    public function syncSupportingContracts($contract_id)
    {
        if (empty($contract_id)) {
            return true;
        }

        DB::table('supporting_contracts')->where('supporting_contract_id', $this->id)->delete();

        $insert = [
            'contract_id'            => $contract_id,
            'supporting_contract_id' => $this->id,
        ];

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

    /**
     * Get the parent contract
     *
     * @return array
     */
    public function getParentContract()
    {
        return (DB::table('supporting_contracts')
                  ->where('supporting_contract_id', $this->id)
                  ->orderBy('id', 'DESC')
                  ->first()) ? DB::table(
            'supporting_contracts'
        )->where('supporting_contract_id', $this->id)->orderBy('id', 'DESC')->first()->contract_id : null;
    }

    /**
     * Get contract Create Date
     *
     * @param string $format
     *
     * @return string
     */
    public function createdDate($format = '')
    {
        if ($format) {
            return translate_date($this->created_datetime->format($format));
        }

        return $this->created_datetime;
    }

    /**
     * Get contract Updated Date
     *
     * @param string $format
     *
     * @return string
     */
    public function updatedDate($format = '')
    {
        if ($format) {
            return translate_date($this->last_updated_datetime->format($format));
        }

        return $this->last_updated_datetime;
    }
}
