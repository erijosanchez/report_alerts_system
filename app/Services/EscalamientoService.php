<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use App\Services\AsignacionAutomaticaService;
use App\Services\AlarmaService;

class EscalamientoService
{
    /**
     * Procesar escalamientos pendientes
     */
    public function procesarEscalamientos()
    {
        // Buscar tickets que llevan mucho tiempo sin resolver
        // Ejemplo: tickets abiertos por más de 2 horas con prioridad Alta/Urgente
        $tickets = Ticket::whereIn('estado', ['abierto', 'en_proceso'])
            ->whereHas('prioridad', function($q) {
                $q->whereIn('nombre', ['Alta', 'Urgente', 'Crítica']);
            })
            ->where('fecha_apertura', '<', now()->subHours(2))
            ->get();

        foreach ($tickets as $ticket) {
            $this->escalarTicket($ticket);
        }
        
        Log::info("Procesados " . $tickets->count() . " tickets para escalamiento");
    }

    /**
     * Escalar un ticket específico
     */
    public function escalarTicket(Ticket $ticket)
    {
        Log::warning("Escalando ticket {$ticket->numero_ticket}");

        // Incrementar contador
        $intentos = ($ticket->intentos_escalamiento ?? 0) + 1;
        
        $ticket->update([
            'intentos_escalamiento' => $intentos
        ]);

        // Si es primer escalamiento, reasignar
        if ($intentos == 1) {
            app(AsignacionAutomaticaService::class)->reasignarTicket(
                $ticket, 
                'Escalamiento por tiempo de espera'
            );
        }

        // Si es segundo o más, crear alarma crítica
        if ($intentos >= 2) {
            app(AlarmaService::class)->crearAlarmaEscalamientoCritico($ticket);
        }
    }
}