<?php namespace App\Nrgi\Mturk\Services;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mturk\Entities\Task;
use App\Nrgi\Mturk\Repositories\TaskRepositoryInterface;
use App\Nrgi\Services\Contract\ContractService;
use App\Nrgi\Services\Contract\Pages\PagesService;
use Exception;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    /**
     * @var TaskRepositoryInterface
     */
    protected $task;
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var Log
     */
    protected $logger;
    /**
     * @var MTurkService
     */
    protected $turk;
    /**
     * @var PagesService
     */
    protected $page;
    /**
     * @var String
     */
    protected $task_url = 'https://task-manish707.rhcloud.com/?pdf=';
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @param TaskRepositoryInterface $task
     * @param ContractService         $contract
     * @param Log                     $logger
     * @param MTurkService            $turk
     * @param PagesService            $page
     * @param Queue                   $queue
     */
    public function __construct(
        TaskRepositoryInterface $task,
        ContractService $contract,
        Log $logger,
        MTurkService $turk,
        PagesService $page,
        Queue $queue
    ) {
        $this->task     = $task;
        $this->contract = $contract;
        $this->logger   = $logger;
        $this->turk     = $turk;
        $this->page     = $page;
        $this->queue    = $queue;
    }

    /**
     * Get Contracts having MTurk Tasks
     *
     * @return Collection|null
     */
    public function getContracts()
    {
        $contracts = $this->contract->getMTurkContracts();

        if(!is_null($contracts)){
            foreach ($contracts as &$contract) {
                $contract->total_hits   = $this->getTotalHits($contract->id);
                $contract->count_status = $this->getTotalByStatus($contract->id);
            }
        }

        return $contracts;
    }

    /**
     * Get MTurk Contract List
     *
     * @return array
     */
    public function getContractsList()
    {
        $contracts = $this->contract->getMTurkContracts();
        $list = [];

        foreach($contracts as $contract)
        {
            $list[$contract->id] = $contract->title;
        }

        return $list;
    }

    /**
     * Create new task
     *
     * @param $contract_id
     * @return bool
     */
    public function create($contract_id)
    {
        $contract = $this->contract->findWithPages($contract_id);

        try {
            $this->task->createTasks($contract->pages);
            $this->logger->info('Tasks added in database', ['Contract_id' => $contract_id]);
        }catch (Exception $e) {
            $this->logger->error('createTasks:'. $e->getMessage(), ['Contract_id' => $contract_id]);
            return false;
        }

        try {
            $contract->mturk_status = Contract::MTURK_SENT;
            $contract->save();
        }catch (Exception $e) {
            $this->logger->error('save:'. $e->getMessage(), ['Contract_id' => $contract->id]);
            return false;
        }

        $this->logger->activity('mturk.log.create', ['contract' => $contract->title], $contract->id);

        $this->queue->push('App\Nrgi\Mturk\Services\Queue\MTurkQueue', ['contract_id' => $contract->id], 'mturk');

        return true;
    }

    /**
     * Mechanical turk process
     *
     * @param $contract_id
     * @return bool
     */
    public function mTurkProcess($contract_id)
    {
        $contract = $this->contract->findWithPages($contract_id);

        if($this->sendToMTurk($contract)) {
            $this->logger->info('Contract sent to MTurk ', ['Contract_id' => $contract->id]);

            return true;
        }

        return false;
    }

    /**
     * Send Tasks to MTurk
     *
     * @param $contract
     * @return bool
     * @throws Exception
     */
    public function sendToMTurk($contract)
    {
        foreach ($contract->pages as $key => $page) {
            $title       = sprintf("Transcription of Contract '%s' - Pg: %s Lang: %s",  str_limit($contract->title, 70), $page->page_no, $contract->metadata->language);
            $url         = $this->task_url . $page->pdf_url;
            $description = config('mturk.defaults.production.Description');

            try{
                $ret    = $this->turk->createHIT($title, $description, $url);
            }catch (Exception $e){
                $this->logger->error('createHIT: '. $e->getMessage(), ['Contract_id' => $contract->id, 'Page' => $page->page_no]);
                continue;
            }

            $update = ['hit_id' => $ret->hit_id, 'hit_type_id' => $ret->hit_type_id];

            if ($ret) {
                $this->task->update($page->contract_id, $page->page_no, $update);
                $this->logger->info('update:'. sprintf('HIT created for page no.%s', $page->page_no) , ['Contract_id' => $contract->id, 'Page' => $page->page_no]);
                continue;
            }

            $this->logger->error(
                'Error while sending to MTurk',
                ['Contract_id' => $page->contract_id, 'Page No.' => $page->page_no]
            );
        }

        return true;
    }

    /**
     * Update assignment to tasks collection
     *
     * @param $tasks
     * @return mixed
     */
    public function appendAssignment($tasks)
    {
        foreach ($tasks as &$task) {
            $task = $this->updateAssignment($task);
        }

        return $tasks;
    }

    /**
     * Save Assignment
     *
     * @param Task $task
     * @return Task
     */
    public function updateAssignment(Task $task)
    {
        try{
            if (empty($task->assignments)) {
                $assignment = $this->turk->assignment($task->hit_id);

                if (!is_null($assignment) && $assignment['TotalNumResults'] > 0) {
                    $task->status      = Task::COMPLETED;
                    $task->assignments = $this->getFormattedAssignment($assignment);
                    $this->logger->info(sprintf('Tasks completed for page no.%s', $task->page_no) , ['Page' => $task->page_no]);
                    $task->save();
                }
            }
        }catch (Exception $e) {
             $this->logger->error($e->getMessage());
        }

        return $task;
    }

    /**
     * Approve Task
     *
     * @param $contract_id
     * @param $task_id
     * @return bool
     */
    public function approveTask($contract_id, $task_id)
    {
        try{
            $task = $this->task->getTask($contract_id, $task_id);
        }catch (Exception $e){
            $this->logger->error($e->getMessage(), ['Contract id' => $contract_id, 'Task' => $task_id]);
            return false;
        }

        if ($task->assignments->assignment->status == 'Submitted') {
            try{
                $response = $this->turk->approve($task->assignments->assignment->assignment_id);
            }catch (Exception $e){
                $this->logger->error($e->getMessage(), ['Contract id' => $contract_id, 'Page' => $task->page_no]);
                return false;
            }

            if ($response['ApproveAssignmentResult']['Request']['IsValid'] == 'True') {
                $assignments                     = $task->assignments;
                $assignments->assignment->status = 'Approved';
                $task->assignments               = $assignments;
                $task->approved                  = Task::APPROVED;

                $this->logger->info(sprintf('Assignment approved for page no.%s', $task->page_no) , ['Page' => $task->page_no]);
                $this->logger->mTurkActivity('mturk.log.approve',null,$contract_id,$task->page_no);

                return  $task->save();
            }
        }

        return false;
    }

    /**
     * Reject Task
     *
     * @param $contract_id
     * @param $task_id
     * @return bool
     */
    public function rejectTask($contract_id, $task_id, $message)
    {
        try{
            $task = $this->task->getTask($contract_id, $task_id);
        } catch (Exception $e){
            $this->logger->error($e->getMessage(), ['Contract id' => $contract_id, 'Task' => $task_id]);
            return false;
        }

        if ($task->assignments->assignment->status == 'Submitted') {
           try{
               $response = $this->turk->reject($task->assignments->assignment->assignment_id, $message);
           } catch(Exception $e)
           {
               $this->logger->error($e->getMessage(), ['Contract id' => $contract_id, 'Task' => $task_id]);
               return false;
           }

            if ($response['RejectAssignmentResult']['Request']['IsValid'] == 'True') {
                $assignments                     = $task->assignments;
                $assignments->assignment->status = 'Rejected';
                $task->assignments               = $assignments;
                $task->approved                  = Task::REJECTED;
                $this->logger->info(sprintf('Assignment rejected for page no.%s', $task->page_no) , ['Page' => $task->page_no]);
                $this->logger->mTurkActivity('mturk.log.reject',null,$contract_id,$task->page_no);

                return $task->save();
            }
        }

    }

    /**
     * Get Task Detail
     *
     * @param $contract_id
     * @param $task_id
     * @return Task|null
     */
    public function get($contract_id, $task_id)
    {
        try{
            $task = $this->task->getTask($contract_id, $task_id);
            return $this->updateAssignment($task);

        }catch (Exception $e)
        {
            $this->logger->info( $e->getMessage() , ['Task' => $task_id]);
            return null;
        }
    }

    /**
     * Reset HIT
     *
     * @param $contract_id
     * @param $id
     * @return bool
     */
    public function resetHIT($contract_id, $task_id)
    {
        $contract = $this->contract->find($contract_id);

        try{
            $task = $this->task->getTask($contract_id, $task_id);
        }catch (Exception $e){
            $this->logger->error($e->getMessage(), [ 'Contract id' => $contract_id,  'Task' => $task_id]);
            return false;
        }

        try{
           if(!$this->turk->deleteHIT($task->hit_id)){
               return false;
           }
        }catch (Exception $e){
            $this->logger->error($e->getMessage(), [ 'Contract id' => $contract_id, 'hit id' => $task->hit_id, 'Task' => $task_id]);
            return false;
        }

        $title       = sprintf("Transcription of Contract '%s' - Pg: %s Lang: %s",  str_limit($contract->title, 70), $task->page_no, $contract->metadata->language);
        $url         = $this->task_url . $task->pdf_url;
        $description = config('mturk.defaults.production.Description');

        try{
            $ret = $this->turk->createHIT($title, $description, $url);
        }catch (Exception $e){
            $this->logger->error($e->getMessage(), [ 'Contract id' => $contract_id,  'Task' => $task_id]);
            return false;
        }
            $update = [
                'hit_id'      => $ret->hit_id,
                'assignments' => null,
                'status'      => 0,
                'approved'    => 0,
                'hit_type_id' => $ret->hit_type_id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($ret) {
                $this->task->update($task->contract_id, $task->page_no, $update);
                $this->logger->info('HIT successfully reset', [ 'Contract id' => $contract_id,  'Task' => $task_id]);

                if(php_sapi_name()!='cli'){
                    $this->logger->mTurkActivity('mturk.log.reset',null,$task->contract_id,$task_id);
                }

                return true;
            }

            $this->logger->error('Error in MTurk', [ 'Contract id' => $contract_id,  'Task' => $task_id]);
            return false;
    }

    /**
     * Get Total Hits
     *
     * @param $contract_id
     * @return mixed
     */
    public function getTotalHits($contract_id)
    {
        return $this->task->getTotalHits($contract_id);
    }

    /**
     * Get total By status
     *
     * @param $status
     * @return array
     */
    public function getTotalByStatus($contract_id)
    {
        return $this->task->getTotalByStatus($contract_id);
    }

    /**
     * Text send to RC
     *
     * @param $contract_id
     * @return bool
     */
    public function copyTextToRC($contract_id)
    {
        $tasks = $this->task->getAll($contract_id);

        foreach ($tasks as $task) {
            $pdf_text = nl2br($task->assignments->assignment->answer);
            $this->page->saveText($contract_id, $task->page_no, $pdf_text, false);
        }

        $this->contract->updateWordFile($contract_id);
        $contract               = $this->contract->find($contract_id);
        $contract->mturk_status = Contract::MTURK_COMPLETE;

        $this->logger->info('Contract text updated from MTurk' , ['Contract id' => $contract_id]);
        $this->logger->activity('mturk.log.sent_to_rc', null, $contract_id);

        return $contract->save();
    }

    /**
     * Get Formatted Assignments
     *
     * @param $assignment
     * @return array
     */
    protected function getFormattedAssignment($assignment)
    {
        $answerObj = json_decode(json_encode(new \SimpleXMLElement($assignment['Assignment']['Answer']), true));
        $data      = [];
        $answer    = '';
        foreach ($answerObj->Answer as $key => $ans) {
            if ($ans->QuestionIdentifier == 'feedback') {
                $answer = $ans->FreeText;
                break;
            }
        }
        $data['total']      = $assignment['TotalNumResults'];
        $data['assignment'] = [
            'assignment_id' => $assignment['Assignment']['AssignmentId'],
            'worker_id'     => $assignment['Assignment']['WorkerId'],
            'accept_time'   => $assignment['Assignment']['AcceptTime'],
            'submit_time'   => $assignment['Assignment']['SubmitTime'],
            'status'        => $assignment['Assignment']['AssignmentStatus'],
            'answer'        => $answer
        ];

        return $data;
    }

    /**
     * Get Approval Pending tasks
     *
     * @param $contract_id
     * @return mixed
     */
    public function getApprovalPendingTask($contract_id)
    {
        return $this->task->getApprovalPendingTask($contract_id);
    }

    /**
     * Get Expired Tasks
     *
     * @return Collection
     */
    public function getExpired()
    {
        return $this->task->getExpired();
    }
}
