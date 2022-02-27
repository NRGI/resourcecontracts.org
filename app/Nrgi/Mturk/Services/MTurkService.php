<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Entities\MturkTaskItem;
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
     * @var MturkTaskItem
     */
    private $mturkTaskItem;

    /**
     * MTurkService constructor.
     *
     * @param Carbon $carbon
     * @param Log    $logger
    * @param MturkTaskItem    $mturkTaskItem
     *
     * @throws MTurkException
     */
    public function __construct(Carbon $carbon, Log $logger, MturkTaskItem $mturkTaskItem)
    {
        parent::__construct();

        $this->carbon = $carbon;
        $this->logger = $logger;
        $this->mturkTaskItem = $mturkTaskItem;
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
            'QualificationRequirements'   => config('mturk.defaults.production.QualificationRequirements'),
            'AutoApprovalDelayInSeconds' => config('mturk.defaults.production.AutoApprovalDelayInSeconds')
        ];

        $result = $this->createHITByExternalQuestion($params);

        if (isset($result['HIT'])) {
            return (object) ['hit_id' => $result['HIT']['HITId'], 'hit_type_id' => $result['HIT']['HITTypeId'], 'description'=>$result['HIT']['Description']];
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
        $this->logger->info(' HIT ID '.json_encode($hit_id));
        $hitResponse         = $this->getHIT($hit_id);
        if($hitResponse['http_code'] == 400) {
            return $hitResponse;
        }
        $hit = $hitResponse['response'];
        $this->logger->info(' HIT'.json_encode( $hit ));
        $status      = $hit['HIT']['HITStatus'];
        $expiry_date = $this->carbon->createFromTimestamp(strtotime($hit['HIT']['Expiration']));
        $isExpired   = $expiry_date->diffInSeconds(null, false) > 1;
        $isRejected  = (isset($task->assignments->assignment->status) && $task->assignments->assignment->status == 'Rejected');
        if ($status == 'Assignable' || $isExpired || $isRejected) {
            $this->updateExpirationForHIT($hit_id, 0);
            $this->deleteHIT($hit_id);
            $hitResponse = $this->getHit($hit_id);
            $this->logger->info(' Hit is '.json_encode( $hitResponse ));
            return $hitResponse;
        }
        $this->logger->info('Returning code without deleting');
        return $hitResponse;
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
     * Returns answer for specific hit
     *
     * @param $task
     *
     * @return string
     */
    public function getAns($task)
    {
        $taskItems = $task->taskItems->toArray();
        foreach($taskItems as $key => $taskItem )
        {
            $this->logger->info('Task item'.json_encode($taskItem));
            $feedback       = '';
           
            if(isset($taskItem->answer)) 
            {
                $db_assignment  = json_decode(json_encode($taskItem->answer), true);
                $this->logger->info('DB assignment'.json_encode($db_assignment));
                $update_ans     = false;
                if (isset($db_assignment)) {
                    if (!is_array($db_assignment)) {
                        $feedback[$taskItem->page_no] = $db_assignment;
                    } elseif (is_array($db_assignment) && isset($db_assignment['answer'])) {
                        $feedback[$taskItem->page_no] = $db_assignment['answer'];
                    }
                }
            }
           



        /*
         * Checks if task assignment have answer in assignment json column
         * The old api saved assignment in assignment json column with format
            {
              "total": "1",
              "assignment": {
                "assignment_id": "3LEIZ60CDKNNV3H5QVA58V0CQBU9ZY",
                "worker_id": "AX2EWYWZM19AZ",
                "accept_time": "2019-03-30T01:13:17Z",
                "submit_time": "2019-04-03T17:57:25Z",
                "status": "Approved",
                "answer": "feedback from worker"
              }
            }
         * The new api saved assignment in assignment json column with format
             {
              "assignment": {
                "assignment_id": "38JBBYETQPYON2KXDD016DOFB1X4EQ",
                "worker_id": "A234QKV52N964W",
                "accept_time": 1567703209,
                "submit_time": 1567847643,
                "status": "Approved",
                "answer": {
                  "QuestionIdentifier": "workerId",
                  "FreeText": "A234QKV52N964W",
                }
              },
              "total": 1
            }
         * If api call returns answer then update the json column with answer for safety
        */


        /*API CALL*/
        $api_assignment = $this->listAssignmentsForHIT($task->hit_id);
        $this->logger->info('API ASSIGNMENT'.json_encode($api_assignment));
        if (array_key_exists('Assignments', $api_assignment) && is_array($api_assignment['Assignments']) && !empty($api_assignment['Assignments'])) {
            $assign = $api_assignment['Assignments'][0];

            if (array_key_exists('Answer', $assign)) {
                $answer  = $assign['Answer'];
                $xml     = simplexml_load_string($answer);
                $json    = json_encode($xml);
                $answers = json_decode($json, true);
                $answers = $answers['Answer'];

                foreach ($answers as $ans) {
                    $this->logger->info('API ASSIGNMENT ANSWER'.json_encode($ans));
                    $answerValue = $ans['QuestionIdentifier'];
                    if (substr($answerValue, 0, strlen('feedback')) == 'feedback') {
                        $values = explode('_', $key);
                        if(count($values) > 1) {
                         
                            $page_no = $values[1];
                            $this->logger->info('API ASSINGMENT PAGE NO.'.json_encode($page_no))
                            $feedback[$page_no] = $ans['FreeText'];
        
                            if (is_array($feedback[$page_no])) {
                                $feedback[$page_no] = $feedback[$page_no][0];
                            }
                            
                            /* The assignment json is updated with answer for safety */
                            if(is_array($db_assignment)) {
                                $db_assignment['answer'] = $feedback[$page_no];
                                $update_ans = true;
                            }
                            break;
                        }
                    }

                }
            }
        }

        /*updates assignment json column with answer if api returns answer*/
        if ($update_ans) {
            $taskItem->answer = json_decode(json_encode($db_assignment));
            $this->logger->info('API ASSINGMENT ANSWER'.json_encode($taskItem->answer))
            $taskItem->save();
        }
    }

        return $feedback;
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
