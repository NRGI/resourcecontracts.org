<?php namespace App\Nrgi\Mturk\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Task
 * @property string   status
 * @property string   approved
 * @property object   assignments
 * @property string   hit_id
 * @property int      page_no
 * @property int      contract_id
 * @package App\Nrgi\Mturk\Entities
 */
class Task extends Model
{
    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'mturk_tasks';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'contract_id',
        'assignments',
        'hit_id',
        'hit_type_id',
        'page_no',
        'text',
        'status',
        'approve'
    ];

    const PENDING   = 0;
    const COMPLETED = 1;

    const APPROVAL_PENDING = 0;
    const APPROVED         = 1;
    const REJECTED         = 2;

    /**
     * Get task Status
     *
     * @return string|null
     */
    public function status()
    {
        $text = ['Pending', 'Completed'];

        return array_key_exists($this->status, $text) ? $text[$this->status] : null;
    }

    /**
     * Get tasks approval status
     *
     * @return string/null
     */
    function approved()
    {
        $text = ['-', 'Approved', 'Rejected'];

        return array_key_exists($this->approved, $text) ? $text[$this->approved] : null;
    }

    /**
     * Select Completed tasks
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', '=', static::COMPLETED);
    }

    /**
     * Select Approved tasks
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApprove($query)
    {
        return $query->where('approved', '=', static::APPROVED);
    }

    /**
     * Select Rejected tasks
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('approved', '=', static::REJECTED);
    }

    /**
     * Select Approval Pending tasks
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApprovalPending($query)
    {
        return $query->where('approved', '=', static::APPROVAL_PENDING);
    }

    /**
     * Get Assignment object
     *
     * @return object
     */
    public function getAssignmentsAttribute($assignments)
    {
        if (!empty($assignments)) {
            return json_decode($assignments);
        }

        return $assignments;
    }

    /**
     * Set assignment as json
     *
     * @param $assignments
     * @return void
     */
    public function setAssignmentsAttribute($assignments)
    {
        $this->attributes['assignments'] = empty($assignments) ? '' : json_encode($assignments);
    }

    /**
     * Select expired tasks
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query)
    {
        $expire_in_sec = config('mturk.defaults.production.AssignmentDurationInSeconds');
        $date          = Carbon::now()->subSeconds($expire_in_sec);

        return $query->where('created_at', '<=', $date->format('Y-m-d H:i:s'));
    }

    /**
     * Select pending tasks
     *
     * @param $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', '=', static::PENDING);
    }

}
