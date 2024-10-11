<?php

namespace App\Console;

use App\Models\System\Configuration;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $configuration = Configuration::first();

        $minutes_send_regularizare = $configuration->minutes_send_regularizare;
        $minutes_verify_cdr = $configuration->minutes_verify_cdr;
        if ($minutes_send_regularizare > 0) {
            $schedule->command('tenancy:run sunat:send-regularizare')
                ->cron('*/' . $minutes_send_regularizare . ' * * * *');
        }
        if ($minutes_verify_cdr > 0) {
            $schedule->command('tenancy:run sunat:cdr-regularizare')
                ->cron('*/' . $minutes_verify_cdr . ' * * * *');
        }
        $schedule->command('tenancy:run reject:out-date')
            ->everyTwoHours();
        // $schedule->command('tenancy:run sunat:verify-cdr')
        //     ->everyTenMinutes();
        // $schedule->command('tenancy:run sunat:send-regularizare')
        //     ->everyFiveMinutes();
        $schedule->command('tenancy:run tenant:run')
            ->everyMinute();
        $schedule->command('tenancy:run pse:check')
            ->everyTenMinutes();
        $schedule->command('tenancy:run pse:send')
            ->cron('*/' . config('configuration.min_send_pse') . ' * * * *');
        // ->everyMinutes(config('configuration.min_send_pse'));
        $schedule->command('tenancy:run pse:dispatchcheck')
            ->everyTenMinutes();
        $schedule->command('status:server')->hourly();
        // Llena las tablas para libro mayor - Se desactiva CMAR - buscar opcion de url
        // $schedule->command('account_ledger:fill')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
