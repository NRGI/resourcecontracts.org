<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

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
        'App\Console\Commands\ChangeFileNameCommand',
        'App\Console\Commands\MturkNotificationCommand',
        'App\Console\Commands\UpdateCorporateGroupList',
        'App\Console\Commands\UpdateMetadata',
        'App\Console\Commands\RenewMTurkTask',
        'App\Console\Commands\CreateMTurkTasks',
        'App\Console\Commands\ResetMTurkTasks',
        'App\Console\Commands\MTurkBalanceNotification',
        'App\Console\Commands\UpdateGovernmentEntities',
        'App\Console\Commands\UpdateAnnotationCategory',
        'App\Console\Commands\UpdateMTurkAssignment',
        'App\Console\Commands\UpdateAnnotationSection',
        'App\Console\Commands\AnnotationHarmonization',
        'App\Console\Commands\BulkDownloadText',
        'App\Console\Commands\ImportContracts',
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if (strtolower(env('APP_ENV')) == 'staging') {
            $schedule->command('migrate');
            $schedule->command('nrgi:mturk')->dailyAt('01:00');
            $schedule->command('nrgi:mturkbalance')->dailyAt('01:30');
            $schedule->command('nrgi:updatemturktasks')->dailyAt('06:00');
            $schedule->command('nrgi:renewmturktask')->dailyAt('02:00');
            $schedule->command('nrgi:updategroup')->dailyAt('10:00');
            $schedule->command('nrgi:updategovernmententities')->dailyAt('02:00');
            $schedule->command('nrgi:bulktext')->weekly();

            $myfile = fopen(public_path('test.txt'), "w");
            fwrite($myfile, date('Y-m-d H:i:s'));
            fclose($myfile);
        }

        if (strtolower(env('APP_ENV')) == 'production') {
            $schedule->command('nrgi:mturk')->dailyAt('01:00');
            $schedule->command('nrgi:mturkbalance')->dailyAt('01:30');
            $schedule->command('nrgi:updatemturktasks')->dailyAt('06:00');
            $schedule->command('nrgi:renewmturktask')->dailyAt('02:00');
            $schedule->command('nrgi:updategroup')->dailyAt('10:00');
            $schedule->command('nrgi:updategovernmententities')->dailyAt('02:00');
            $schedule->command('nrgi:bulktext')->weekly();
        }
    }
}
