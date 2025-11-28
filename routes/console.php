<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Monitorear sedes cada 5 minutos
Schedule::command('monitoreo:sedes')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/monitoreo.log'));

// Procesar escalamientos cada 15 minutos
Schedule::command('tickets:escalar')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/escalamiento.log'));

// Solo durante horario laboral (opcional)
Schedule::command('tickets:escalar')
    ->everyFifteenMinutes()
    ->between('8:00', '18:00')  // Solo de 8 AM a 6 PM
    ->weekdays()                 // Solo días de semana
    ->withoutOverlapping()
    ->runInBackground();

// Verificar tickets críticos más frecuente (cada 5 minutos)
Schedule::call(function () {
    $criticos = \App\Models\Ticket::whereIn('estado', ['abierto', 'en_proceso'])
        ->whereHas('prioridad', function($q) {
            $q->where('nombre', 'ILIKE', '%crítica%')
              ->orWhere('nombre', 'ILIKE', '%urgente%');
        })
        ->count();
    
    if ($criticos > 0) {
        \Log::warning("⚠️ {$criticos} tickets críticos/urgentes abiertos");
    }
})->everyFiveMinutes();

// Log de sistema cada hora
Schedule::call(function () {
    $stats = [
        'tickets_abiertos' => \App\Models\Ticket::whereIn('estado', ['abierto', 'en_proceso'])->count(),
        'alarmas_activas' => \App\Models\Alarma::where('activa', true)->count(),
        'sedes_offline' => \App\Models\Sede::where('estado_conexion', 'offline')->count(),
    ];
    
    \Log::info('📊 Stats del sistema: ' . json_encode($stats));
})->hourly();