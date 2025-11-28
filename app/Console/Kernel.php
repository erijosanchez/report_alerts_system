<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\MonitorearSedesCommand::class,
        Commands\ProcesarEscalamientosCommand::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Monitorear sedes cada 5 minutos
        $schedule->command('monitoreo:sedes')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Procesar escalamientos cada 15 minutos
        $schedule->command('tickets:escalar')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground();
    }
}
