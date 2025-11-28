<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MonitoreoService;
use Illuminate\Console\Scheduling\Schedule;

class MonitorearSedesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoreo:sedes';
    protected $description = 'Monitorear todas las sedes activas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando monitoreo de sedes...');
        
        $monitoreoService = app(MonitoreoService::class);
        $monitoreoService->monitorearSedes();
        
        $this->info('Monitoreo completado.');
    }
}
