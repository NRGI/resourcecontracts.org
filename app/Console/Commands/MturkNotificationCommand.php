<?php namespace App\Console\Commands;

use App\Nrgi\Entities\Contract\Contract;
use App\Nrgi\Mturk\Services\MTurkNotificationService;
use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class MturkNotificationCommand
 * @package App\Console\Commands
 */
class MturkNotificationCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nrgi:mturk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send mturk contracts status email to user.';
    /**
     * @var ContractService
     */
    protected $contract;
    /**
     * @var MTurkNotificationService
     */
    protected $notify;

    /**
     * Create a new command instance.
     *
     * @param ContractService          $contract
     * @param MTurkNotificationService $notify
     */
    public function __construct(ContractService $contract, MTurkNotificationService $notify)
    {
        parent::__construct();
        $this->contract = $contract;
        $this->notify   = $notify;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $contracts = $this->contract->getMTurkContracts(['status' => Contract::MTURK_SENT]);
        foreach ($contracts as $contract) {
            $this->notify->process($contract->id);
        }

        $file = storage_path().'/logs/scheduler.log';
        Log::useFiles($file);
        Log::info('Mturk command successfully executed. MTurk status of contracts send via email.');
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

}
