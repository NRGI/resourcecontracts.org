<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\Contract\ImportService;

/**
 * Class ContractDownloadQueue
 * @package App\Nrgi\Services\Queue
 */
class ContractDownloadQueue
{
    /**
     * @var ImportService
     */
    protected $import;

    /**
     * @param ImportService $import
     */
    public function __construct(ImportService $import)
    {
        $this->import = $import;
    }

    /**
     * Start download queue
     *
     * @param $job
     * @param $data
     */
    public function fire($job, $data)
    {
        $this->import->download($data['import_key'], $data['one_drive_data']);
        $job->delete();
    }
}
