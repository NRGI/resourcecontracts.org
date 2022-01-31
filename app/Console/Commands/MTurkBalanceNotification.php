<?php namespace App\Console\Commands;

use App\Nrgi\Mturk\Services\MTurkNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Class MTurkBalance
 * @package App\Console\Commands
 */
class MTurkBalanceNotification extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'nrgi:mturkbalance';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send MTurk low balance notification';

	/**
	 * Create a new command instance.
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 * @param MTurkNotificationService $mturk
	 */
	public function fire(MTurkNotificationService $mturk)
	{
		$mturk->checkBalance();

		$file = storage_path().'/logs/scheduler.log';
		Log::useFiles($file);
		Log::info("MTurk balance check command successfully executed.");
	}

}
