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
        Log::info('🔍 Iniciando verificación de escalamientos...');

        // 1. CRÍTICO/URGENTE: Primera respuesta en 30 min, resolución en 4 horas
        $this->procesarEscalamientoPorPrioridad('crítica|urgente', [
            'primera_respuesta' => 30,  // minutos
            'resolucion' => 240,         // minutos (4 horas)
            'segundo_escalamiento' => 120 // minutos (2 horas después del primero)
        ]);

        // 2. ALTA: Primera respuesta en 2 horas, resolución en 8 horas
        $this->procesarEscalamientoPorPrioridad('alta', [
            'primera_respuesta' => 120,  // minutos (2 horas)
            'resolucion' => 480,          // minutos (8 horas)
            'segundo_escalamiento' => 180 // minutos (3 horas después del primero)
        ]);

        // 3. MEDIA: Primera respuesta en 4 horas, resolución en 24 horas
        $this->procesarEscalamientoPorPrioridad('media', [
            'primera_respuesta' => 240,  // minutos (4 horas)
            'resolucion' => 1440,         // minutos (24 horas)
            'segundo_escalamiento' => 360 // minutos (6 horas después del primero)
        ]);

        // 4. BAJA: Primera respuesta en 8 horas, resolución en 72 horas
        $this->procesarEscalamientoPorPrioridad('baja', [
            'primera_respuesta' => 480,   // minutos (8 horas)
            'resolucion' => 4320,         // minutos (72 horas)
            'segundo_escalamiento' => 720 // minutos (12 horas después del primero)
        ]);

        Log::info("✅ Verificación de escalamientos completada");
    }

    /**
     * Procesar escalamiento por prioridad
     */
    private function procesarEscalamientoPorPrioridad($prioridad, $tiempos)
    {
        // ESCALAMIENTO 1: Sin primera respuesta en el tiempo SLA
        $ticketsPrimerEscalamiento = Ticket::whereIn('estado', ['abierto', 'en_proceso'])
            ->whereHas('prioridad', function($q) use ($prioridad) {
                foreach (explode('|', $prioridad) as $nombre) {
                    $q->orWhere('nombre', 'ILIKE', "%{$nombre}%");
                }
            })
            ->where('fecha_apertura', '<', now()->subMinutes($tiempos['primera_respuesta']))
            ->where(function($q) {
                $q->whereNull('intentos_escalamiento')
                  ->orWhere('intentos_escalamiento', 0);
            })
            ->get();

        foreach ($ticketsPrimerEscalamiento as $ticket) {
            $this->escalarTicket($ticket, "SLA de respuesta vencido ({$prioridad})");
        }

        // ESCALAMIENTO 2: Ya escalado una vez, pero sigue sin resolver
        $ticketsSegundoEscalamiento = Ticket::whereIn('estado', ['abierto', 'en_proceso'])
            ->whereHas('prioridad', function($q) use ($prioridad) {
                foreach (explode('|', $prioridad) as $nombre) {
                    $q->orWhere('nombre', 'ILIKE', "%{$nombre}%");
                }
            })
            ->where('intentos_escalamiento', 1)
            ->where('updated_at', '<', now()->subMinutes($tiempos['segundo_escalamiento']))
            ->get();

        foreach ($ticketsSegundoEscalamiento as $ticket) {
            $this->escalarTicket($ticket, "SLA de resolución en riesgo ({$prioridad})");
        }

        // ESCALAMIENTO 3: SLA de resolución vencido
        $ticketsSLAVencido = Ticket::whereIn('estado', ['abierto', 'en_proceso'])
            ->whereHas('prioridad', function($q) use ($prioridad) {
                foreach (explode('|', $prioridad) as $nombre) {
                    $q->orWhere('nombre', 'ILIKE', "%{$nombre}%");
                }
            })
            ->where('fecha_apertura', '<', now()->subMinutes($tiempos['resolucion']))
            ->where('intentos_escalamiento', '>=', 2)
            ->get();

        foreach ($ticketsSLAVencido as $ticket) {
            $this->escalarTicket($ticket, "⚠️ SLA DE RESOLUCIÓN VENCIDO ({$prioridad})");
        }
    }

    /**
     * Escalar un ticket específico
     */
    private function escalarTicket(Ticket $ticket, $razon)
    {
        Log::warning("⬆️ Escalando ticket {$ticket->numero_ticket} - Razón: {$razon}");

        // Incrementar contador
        $intentos = ($ticket->intentos_escalamiento ?? 0) + 1;
        
        $ticket->update([
            'intentos_escalamiento' => $intentos
        ]);

        // Primer escalamiento: Reasignar a técnico senior
        if ($intentos == 1) {
            Log::info("🔄 Primer escalamiento - Reasignando a técnico senior");
            app(AsignacionAutomaticaService::class)->reasignarTicket($ticket, $razon);
        }

        // Segundo escalamiento: Alarma crítica + notificar admins
        if ($intentos == 2) {
            Log::critical("🚨 Segundo escalamiento - Escalando a supervisores");
            app(AlarmaService::class)->crearAlarmaEscalamientoCritico($ticket);
            app(AsignacionAutomaticaService::class)->reasignarTicket($ticket, $razon);
        }

        // Tercer escalamiento o más: Máxima prioridad
        if ($intentos >= 3) {
            Log::critical("🔴 SLA VENCIDO - Escalamiento a management");
            app(AlarmaService::class)->crearAlarmaEscalamientoCritico($ticket);
            // Aquí podrías notificar a directivos, crear reportes, etc.
        }
    }
}