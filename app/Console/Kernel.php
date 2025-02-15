<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        //Front-End
        $this->load(__DIR__.'/Commands/FrontEnd');

        //Back-End
        $this->load(__DIR__.'/Commands/BackEnd');

        //Customs
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
