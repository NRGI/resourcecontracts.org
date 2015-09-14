<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\Inspire',
        'App\Console\Commands\ProcessDocument',
        'App\Console\Commands\MovePdfToFolder',
        'App\Console\Commands\GenerateWordFile',
        'App\Console\Commands\BulkIndex',
        'App\Console\Commands\MigrateFromDocumentCloud',
        'App\Console\Commands\MigrateEthiopianContracts',
        'App\Console\Commands\ChangeFileNameCommand',
        'App\Console\Commands\MturkNotificationCommand',
        'App\Console\Commands\UpdateCorporateGroupList',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('nrgi:mturk')->dailyAt('13:00');
        $schedule->command('nrgi:updategroup')->dailyAt('10:00');
    }
}
