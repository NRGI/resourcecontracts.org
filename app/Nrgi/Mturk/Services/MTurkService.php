<?php namespace App\Nrgi\Mturk\Services;

/**
 * Class MTurkService
 * @package App\Nrgi\Mturk\Services
 */
class MTurkService extends MechanicalTurk
{
    public function __construct()
    {
        parent::__construct();

        if ($this->isSandbox()) {
            $this->setSandboxMode();
        }
    }

    /**
     * Get MTurk Balance
     *
     * @return object
     */
    public function getBalance()
    {
        $balance = $this->getAccountBalance();

        return (object) $balance['GetAccountBalanceResult']['AvailableBalance'];
    }

    /**
     * Create HIT
     *
     * @param $title
     * @param $description
     * @param $question_url
     * @return bool|object
     */
    public function createHIT($title, $description, $question_url)
    {
        $params = [
            'Description'           => $description,
            'Question'              => $this->getQuestionXML($question_url),
            'SignatureVersion'      => '1',
            'Title'                 => $title,
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
     * @param $hit_id
     * @return bool
     */
    public function deleteHIT($hit_id)
    {
        $data = $this->disableHIT(['HITId' => $hit_id]);

        return $data['DisableHITResult']['Request']['IsValid'] == "True" ? true : false;
    }

    /**
     * Get Assignments
     *
     * @param $hit_id
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
     * @return array
     */
    public function approve($assignment_id, $feedback = '')
    {
        $params = [
            'AssignmentId'      => $assignment_id,
            'RequesterFeedback' => $feedback
        ];

        return $this->approveAssignment($params);
    }

    /**
     * Reject Assignment
     *
     * @param        $assignment_id
     * @param string $feedback
     * @return array
     */
    public function reject($assignment_id, $feedback = '')
    {
        $params = [
            'AssignmentId'      => $assignment_id,
            'RequesterFeedback' => $feedback
        ];

        return $this->rejectAssignment($params);
    }

    /**
     * Get Question XML format
     *
     * @param $url
     * @return string
     */
    protected function getQuestionXML($url)
    {
        return '<ExternalQuestion xmlns="http://mechanicalturk.amazonaws.com/AWSMechanicalTurkDataSchemas/2006-07-14/ExternalQuestion.xsd"><ExternalURL>' . $url . '</ExternalURL><FrameHeight>800</FrameHeight></ExternalQuestion>';
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
