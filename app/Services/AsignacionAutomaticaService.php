<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log;
use App\Services\NotificacionService;

class AsignacionAutomaticaService
{
    /**
     * Asignar automáticamente un ticket al técnico más adecuado
     */
    public function asignarTicket(Ticket $ticket)
    {
        // Buscar técnico disponible
        $tecnico = $this->buscarTecnicoOptimo($ticket);

        if (!$tecnico) {
            Log::warning("No hay técnicos disponibles para ticket {$ticket->numero_ticket}");
            return false;
        }

        // Asignar ticket
        $ticket->update([
            'tecnico_id' => $tecnico->id,
            'estado' => 'en_proceso'
        ]);

        // Crear notificación
        app(NotificacionService::class)->notificarAsignacion($ticket, $tecnico);

        Log::info("Ticket {$ticket->numero_ticket} asignado automáticamente a {$tecnico->nombre}");

        return true;
    }

    /**
     * Buscar el técnico más óptimo (con menos tickets asignados)
     */
    private function buscarTecnicoOptimo(Ticket $ticket)
    {
        // Obtener técnicos activos
        $tecnicos = Usuario::where('rol', 'tecnico')
            ->where('estado', true)
            ->withCount(['ticketsAsignados' => function ($query) {
                $query->whereIn('estado', ['abierto', 'en_proceso']);
            }])
            ->orderBy('tickets_asignados_count', 'asc')
            ->first();

        return $tecnicos;
    }

    /**
     * Reasignar ticket a otro técnico (escalamiento)
     */
    public function reasignarTicket(Ticket $ticket, $razon = 'escalamiento')
    {
        $tecnicoAnterior = $ticket->tecnico_id;

        // Buscar nuevo técnico (excluir el anterior)
        $nuevoTecnico = Usuario::where('rol', 'tecnico')
            ->where('estado', true)
            ->where('id', '!=', $tecnicoAnterior)
            ->withCount(['ticketsAsignados' => function ($query) {
                $query->whereIn('estado', ['abierto', 'en_proceso']);
            }])
            ->orderBy('tickets_asignados_count', 'asc')
            ->first();

        if (!$nuevoTecnico) {
            Log::error("No hay técnicos para reasignar ticket {$ticket->numero_ticket}");
            return false;
        }

        $ticket->update([
            'tecnico_id' => $nuevoTecnico->id,
            'intentos_escalamiento' => ($ticket->intentos_escalamiento ?? 0) + 1
        ]);

        // Crear alarma de escalamiento
        app(AlarmaService::class)->crearAlarmaEscalamiento($ticket, $razon);

        // Notificar al nuevo técnico
        app(NotificacionService::class)->notificarAsignacion($ticket, $nuevoTecnico);

        Log::info("Ticket {$ticket->numero_ticket} reasignado por {$razon}");

        return true;
    }
}
