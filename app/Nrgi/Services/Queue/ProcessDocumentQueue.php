<?php
/**
 * Created by PhpStorm.
 * User: manoj
 * Date: 6/11/15
 * Time: 10:49 AM
 */

namespace App\Nrgi\Services\Queue;

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
    function __construct(ProcessService $process)
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