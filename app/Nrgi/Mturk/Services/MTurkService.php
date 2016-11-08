<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Mturk\Entities\Task;
use Carbon\Carbon;

/**
 * Class MTurkService
 * @package App\Nrgi\Mturk\Services
 */
class MTurkService extends MechanicalTurk
{
    /**
     * @var Carbon
     */
    protected $carbon;

    /**
     * MTurkService constructor.
     *
     * @param Carbon $carbon
     */
    public function __construct(Carbon $carbon)
    {
        parent::__construct();

        if ($this->isSandbox()) {
            $this->setSandboxMode();
        }
        $this->carbon = $carbon;
    }

    /**
     * Get MTurk Balance
     *
     * @return object
     */
    public function getBalance()
    {
        $balance = $this->getAccountBalance();

        return $balance['GetAccountBalanceResult']['AvailableBalance'];
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
            'Description'           => $description,
            'Question'              => $this->getQuestionXML($question_url),
            'SignatureVersion'      => '1',
            'Title'                 => str_limit($title, 128),
            "Reward.1.Amount"       => config('mturk.defaults.production.Reward.Amount'),
            "Reward.1.CurrencyCode" => config('mturk.defaults.production.Reward.CurrencyCode'),
        ];

        $result = $this->createHITByExternalQuestion($params);

        if ($result['HIT']['Request']['IsValid'] == 'True') {
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
        $hit         = $this->getHIT(['HITId' => $hit_id]);
        $status      = $hit['HIT']['HITStatus'];
        $expiry_date = $this->carbon->createFromTimestamp(strtotime($hit['HIT']['Expiration']));
        $isExpired   = $expiry_date->diffInSeconds(null, false) > 1;
        $isRejected  = (isset($task->assignments->assignment->status) && $task->assignments->assignment->status == 'Rejected');

        if ($status == 'Assignable' || $isExpired || $isRejected) {
            $this->forceExpireHIT(['HITId' => $hit_id]);
            $dispose = $this->disposeHIT(['HITId' => $hit_id]);

            return ($dispose['DisposeHITResult']['Request']['IsValid'] == "True");
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

        $result = $this->GetAssignmentsForHIT(['HITId' => $hit_id]);

        return $result['GetAssignmentsForHITResult'];
    }

    /**
     * Approve assignment
     *
     * @param        $assignment_id
     * @param string $feedback
     *
     * @return array
     */
    public function approve($assignment_id, $feedback = '')
    {
        $params = [
            'AssignmentId'      => $assignment_id,
            'RequesterFeedback' => $feedback,
        ];

        return $this->approveAssignment($params);
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
        $params = [
            'AssignmentId'      => $assignment_id,
            'RequesterFeedback' => $feedback,
        ];

        return $this->rejectAssignment($params);
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

    /**
     * Sandbox or Production
     *
     * @return bool
     */
    protected function isSandbox()
    {
        return config('mturk.sandbox_mode');
    }
}
