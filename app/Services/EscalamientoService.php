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
        // Buscar tickets que necesitan escalamiento
        $tickets = Ticket::whereIn('estado', ['abierto', 'en_proceso'])
            ->where(function ($q) {
                // SLA de respuesta vencido sin primera respuesta
                $q->where('fecha_limite_respuesta', '<', now())
                    ->whereNull('fecha_primera_respuesta');
            })
            ->orWhere(function ($q) {
                // SLA de resolución vencido sin resolución
                $q->where('fecha_limite_resolucion', '<', now())
                    ->whereNull('fecha_resolucion');
            })
            ->get();

        foreach ($tickets as $ticket) {
            $this->escalarTicket($ticket);
        }
    }

    /**
     * Escalar un ticket específico
     */
    public function escalarTicket(Ticket $ticket)
    {
        // Determinar tipo de escalamiento
        $tipoEscalamiento = $this->determinarTipoEscalamiento($ticket);

        Log::warning("Escalando ticket {$ticket->numero_ticket} - Razón: {$tipoEscalamiento}");

        // Incrementar contador
        $ticket->increment('intentos_escalamiento');

        // Si es primer escalamiento, reasignar
        if ($ticket->intentos_escalamiento == 1) {
            app(AsignacionAutomaticaService::class)->reasignarTicket($ticket, $tipoEscalamiento);
        }

        // Si es segundo o más, crear alarma crítica
        if ($ticket->intentos_escalamiento >= 2) {
            app(AlarmaService::class)->crearAlarmaEscalamientoCritico($ticket);
        }

        // Calcular próximo escalamiento
        $ticket->update([
            'fecha_proximo_escalamiento' => now()->addHours(2)
        ]);
    }

    /**
     * Determinar el tipo de escalamiento
     */
    private function determinarTipoEscalamiento(Ticket $ticket)
    {
        if (!$ticket->fecha_primera_respuesta && $ticket->fecha_limite_respuesta < now()) {
            return 'SLA de respuesta vencido';
        }

        if (!$ticket->fecha_resolucion && $ticket->fecha_limite_resolucion < now()) {
            return 'SLA de resolución vencido';
        }

        return 'Escalamiento por tiempo';
    }
}
