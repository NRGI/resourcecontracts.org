<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Mturk\Entities\Task;
use Carbon\Carbon;
use Illuminate\Contracts\Logging\Log;

/**
 * Class MTurkService
 * @package App\Nrgi\Mturk\Services
 */
class MTurkService extends MechanicalTurkV2
{
    /**
     * @var Carbon
     */
    protected $carbon;
    /**
     * @var Log
     */
    private $logger;

    /**
     * MTurkService constructor.
     *
     * @param Carbon $carbon
     * @param Log    $logger
     *
     * @throws MTurkException
     */
    public function __construct(Carbon $carbon, Log $logger)
    {
        parent::__construct();

        if ($this->isSandbox()) {
            $this->setSandboxMode();
        }
        $this->carbon = $carbon;
        $this->logger = $logger;
    }

    /**
     * Sandbox or Production
     *
     * @return bool
     */
    protected function isSandbox()
    {
        return config('mturk.sandbox_mode');
    }

    /**
     * Get MTurk Balance
     *
     * @return object
     */
    public function getBalance()
    {
        try {
            $balance = $this->getAccountBalance();

            return $balance['AvailableBalance'];
        } catch (\Exception $e) {
            $dt = Carbon::now();
            $this->logger->error($e->getMessage());
            $log  = new \Illuminate\Support\Facades\Log();
            $file = storage_path().'/logs/'.'mturk-'.$dt->format("Y-m-d").'.log';
            $log::useFiles($file);
            $log::info($e->getMessage());

            return 0;
        }
    }

    /**
     * Create HIT
     *
     * @param $title
     * @param $description
     * @param $question_url
     *
     * @return bool|object
     */
    public function createHIT($title, $description, $question_url)
    {
        $params = [
            'Title'                       => str_limit($title, 128),
            'Description'                 => $description,
            "Reward"                      => config('mturk.defaults.production.Reward'),
            'AssignmentDurationInSeconds' => config('mturk.defaults.production.AssignmentDurationInSeconds'),
            'LifetimeInSeconds'           => config('mturk.defaults.production.LifetimeInSeconds'),
            'Question'                    => $this->getQuestionXML($question_url),
            'MaxAssignments'              => config('mturk.defaults.production.MaxAssignments'),
        ];

        $result = $this->createHITByExternalQuestion($params);

        if (isset($result['HIT'])) {
            return (object) ['hit_id' => $result['HIT']['HITId'], 'hit_type_id' => $result['HIT']['HITTypeId']];
        }

        return false;
    }

    /**
     * Remove HIT from MTurk
     *
     * @param Task $task
     *
     * @return bool
     */
    public function removeHIT(Task $task)
    {
        /*
         * Available options
         * disableHIT (-) : we used this option but did returned money back to account.
         * disposeHIT
         * forceExpireHIT
         * */

        $hit_id      = $task->hit_id;
        $hit         = $this->getHIT($hit_id);
        $status      = $hit['HIT']['HITStatus'];
        $expiry_date = $this->carbon->createFromTimestamp(strtotime($hit['HIT']['Expiration']));
        $isExpired   = $expiry_date->diffInSeconds(null, false) > 1;
        $isRejected  = (isset($task->assignments->assignment->status) && $task->assignments->assignment->status == 'Rejected');

        if ($status == 'Assignable' || $isExpired || $isRejected) {
            $this->updateExpirationForHIT($hit_id, 0);
            $this->deleteHIT($hit_id);
            $hit = $this->getHit($hit_id);

            return ($hit['HIT']['HITStatus'] == "Disposed");
        }

        return false;
    }

    /**
     * Get Assignments
     *
     * @param $hit_id
     *
     * @return array|null
     */
    public function assignment($hit_id)
    {
        if (empty($hit_id)) {
            return null;
        }

        return $this->listAssignmentsForHIT($hit_id);
    }

    /**
     * Approve assignment
     *
     * @param        $assignment_id
     *
     * @return array
     */
    public function approve($assignment_id)
    {
        return $this->approveAssignment($assignment_id);
    }

    /**
     * Reject Assignment
     *
     * @param        $assignment_id
     * @param string $feedback
     *
     * @return array
     */
    public function reject($assignment_id, $feedback = '')
    {
        return $this->rejectAssignment($assignment_id, $feedback);
    }

    /**
     * Get Question XML format
     *
     * @param $url
     *
     * @return string
     */
    protected function getQuestionXML($url)
    {
        return '<ExternalQuestion xmlns="http://mechanicalturk.amazonaws.com/AWSMechanicalTurkDataSchemas/2006-07-14/ExternalQuestion.xsd"><ExternalURL>'.$url.'</ExternalURL><FrameHeight>800</FrameHeight></ExternalQuestion>';
    }
}
