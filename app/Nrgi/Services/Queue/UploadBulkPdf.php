<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\Contract\ImportService;

class UploadBulkPdf
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
        $this->import->uploadPdfToS3AndCreateContracts($data['key']);
        $job->delete();
    }
}
