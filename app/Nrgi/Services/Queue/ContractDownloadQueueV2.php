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
    public $timeout = 60 * 10;
    protected $import_data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($import_data)
    {
        //
        $this->import_data = $import_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImportService $import)
    {
        //
        $data = $this->import_data;
        $import->download($data['import_key'], $data['one_drive_data']);

    }
}
