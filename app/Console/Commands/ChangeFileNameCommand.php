<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as Storage;

/**
 * updates file name of s3 storage
 *
 * Class ChangeFileNameCommand
 * @package App\Console\Commands
 */
class ChangeFileNameCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:changefilename';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'updates filename of all contracts.';
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var Storage
     */
    protected $storage;

    /**
     * Create a new command instance.
     *
     * @param ContractService $contract
     * @param Storage         $storage
     */
    public function __construct(ContractService $contract, Storage $storage)
    {
        parent::__construct();
        $this->contract = $contract;
        $this->storage  = $storage;
    }

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $contracts = Contract::all();
        foreach ($contracts as $key => $contract) {
            $contractDir = $contract->id;
            if ($this->storage->disk('s3')->exists("$contractDir/{$contract->file}")) {
                try {
                    if ($this->renameS3ContractFileName($contract)) {
                        $this->contract->updateFileName($contract);
                        $this->info("done moving {$contractDir}/{$contract->file}");
                    }
                } catch (\Exception $e) {
                    $message = $e->getMessage();
                    $this->error("error moving {$contractDir}/{$contract->file}:$message");
                }
            } else {
                $this->error("{$contractDir}/{$contract->file} file does not exists");
            }

        }
        $this->call('nrgi:bulkindex');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

    /**
     * Renames contract filename
     * @param $contract
     *
     * return bool
     */
    protected function renameS3ContractFileName($contract)
    {
        $newFileName = sprintf("%s-%s", $contract->id, $contract->Slug);
        $contractDir = $contract->id;
        $from        = sprintf("%s/%s", $contractDir, $contract->file);
        $to          = sprintf("%s/%s.pdf", $contractDir, $newFileName);
        if (!$this->storage->disk('s3')->move($from, $to)) {
            return false;
        }
        $filename     = explode('.', $contract->file);
        $filename     = $filename[0];
        $wordFileName = sprintf('%s.docx', $filename);
        $wordFileFrom = sprintf("%s/%s", $contractDir, $wordFileName);
        $wordFileTo   = sprintf("%s/%s.docx", $contractDir, $newFileName);
        if (!$this->storage->disk('s3')->move($wordFileFrom, $wordFileTo)) {
            return false;
        }

        return true;
    }

}
