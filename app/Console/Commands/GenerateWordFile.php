<?php namespace App\Console\Commands;

use App\Nrgi\Services\Contract\ContractService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class GenerateWordFile
 * @package App\Console\Commands
 */
class GenerateWordFile extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'move:word';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate and copy word file to S3.';
	/**
	 * @var ContractService
	 */
	protected $contract;

	/**
	 * Create a new command instance.
	 * @param ContractService $contract
	 */
	public function __construct(ContractService $contract)
	{
		parent::__construct();
		$this->contract = $contract;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$contract_id = $this->input->getOption('id');
		$this->contract->generateWordFile($contract_id);
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
		return [
			['id', null, InputOption::VALUE_OPTIONAL, 'ID of the contract', null],
		];
	}

}
