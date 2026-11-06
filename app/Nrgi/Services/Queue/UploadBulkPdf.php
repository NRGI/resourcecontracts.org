<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\Contract\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadBulkPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $key;

    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Execute the job
     *
     * @param ImportService $import
     */
    public function handle(ImportService $import): void
    {
        $import->uploadPdfToS3AndCreateContracts($this->key);
    }
}
