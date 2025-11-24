<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EscalamientoService;

class ProcesarEscalamientosCommand extends Command
{
    protected $signature = 'tickets:escalar';
    protected $description = 'Procesar escalamientos de tickets pendientes';

    public function handle()
    {
        $this->info('Procesando escalamientos...');
        
        $escalamientoService = app(EscalamientoService::class);
        $escalamientoService->procesarEscalamientos();
        
        $this->info('Escalamientos procesados.');
    }
}
