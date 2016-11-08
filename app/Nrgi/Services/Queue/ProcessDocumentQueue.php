<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\Contract\Page\ProcessService;

/**
 * Class ProcessDocumentQueue
 * @package App\Services\Queue
 */
class ProcessDocumentQueue
{
    /**
     * @var ProcessService
     */
    protected $process;

    /**
     * Constructor
     * @param ProcessService $process
     */
    public function __construct(ProcessService $process)
    {
        $this->process = $process;
    }

    /**
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->process->execute($data['contract_id']);
        $job->delete();
    }
}
