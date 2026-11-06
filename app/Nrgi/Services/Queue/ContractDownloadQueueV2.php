<?php

namespace App\Nrgi\Services\Queue;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Nrgi\Services\Contract\ImportService;

class ContractDownloadQueueV2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes

    private array $importData;

    /**
     * Create a new job instance.
     *
     * @param array $importData
     */
    public function __construct(array $importData)
    {
        $this->importData = $importData;
    }

    /**
     * Execute the job.
     *
     * @param ImportService $import
     */
    public function handle(ImportService $import): void
    {
        $data = $this->importData;
        $import->download($data['import_key'], $data['one_drive_data']);
    }
}
