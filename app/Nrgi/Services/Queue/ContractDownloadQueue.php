<?php namespace App\Nrgi\Services\Queue;

use App\Nrgi\Services\Contract\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ContractDownloadQueue
 */
class ContractDownloadQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $importKey;
    private array $oneDriveData;

    /**
     * @param string $importKey
     * @param array $oneDriveData
     */
    public function __construct(string $importKey, array $oneDriveData)
    {
        $this->importKey = $importKey;
        $this->oneDriveData = $oneDriveData;
    }

    /**
     * Execute the job
     *
     * @param ImportService $import
     */
    public function handle(ImportService $import): void
    {
        $import->download($this->importKey, $this->oneDriveData);
    }
}
