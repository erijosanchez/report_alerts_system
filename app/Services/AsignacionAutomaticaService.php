<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TecnicoDisponibilidad;
use App\Models\Usuario;
use Illuminate\Support\Facades\Log; 
use App\Services\AlarmaService;
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
            'fecha_asignacion' => now(),
            'estado' => 'en_proceso'
        ]);
        
        // Actualizar disponibilidad del técnico
        $disponibilidad = TecnicoDisponibilidad::where('tecnico_id', $tecnico->id)->first();
        if ($disponibilidad) {
            $disponibilidad->incrementarTicketsAsignados();
        }
        
        // Crear notificación
        app(NotificacionService::class)->notificarAsignacion($ticket, $tecnico);
        
        Log::info("Ticket {$ticket->numero_ticket} asignado automáticamente a {$tecnico->nombre}");
        
        return true;
    }
    
    /**
     * Buscar el técnico más óptimo según algoritmo de asignación
     */
    private function buscarTecnicoOptimo(Ticket $ticket)
    {
        // Algoritmo de asignación inteligente
        $tecnicos = TecnicoDisponibilidad::with('tecnico')
            ->where('disponible', true)
            ->whereColumn('tickets_asignados', '<', 'capacidad_maxima')
            ->get();
        
        if ($tecnicos->isEmpty()) {
            return null;
        }
        
        // Ordenar por: 1) menos tickets, 2) última asignación más antigua, 3) nivel
        $tecnicoOptimo = $tecnicos->sortBy(function ($disp) {
            return [
                $disp->tickets_asignados,
                $disp->ultima_asignacion ? $disp->ultima_asignacion->timestamp : 0,
                -$disp->nivel_prioridad
            ];
        })->first();
        
        return $tecnicoOptimo->tecnico;
    }
    
    /**
     * Reasignar ticket a otro técnico (escalamiento)
     */
    public function reasignarTicket(Ticket $ticket, $razon = 'escalamiento')
    {
        $tecnicoAnterior = $ticket->tecnico_id;
        
        // Liberar el técnico anterior
        if ($tecnicoAnterior) {
            $disponibilidad = TecnicoDisponibilidad::where('tecnico_id', $tecnicoAnterior)->first();
            if ($disponibilidad) {
                $disponibilidad->decrementarTicketsAsignados();
            }
        }
        
        // Buscar nuevo técnico (excluir el anterior)
        $nuevoTecnico = TecnicoDisponibilidad::with('tecnico')
            ->where('disponible', true)
            ->where('tecnico_id', '!=', $tecnicoAnterior)
            ->whereColumn('tickets_asignados', '<', 'capacidad_maxima')
            ->orderBy('nivel_prioridad', 'desc') // Priorizar técnicos senior en escalamiento
            ->first();
        
        if (!$nuevoTecnico) {
            Log::error("No hay técnicos para reasignar ticket {$ticket->numero_ticket}");
            return false;
        }
        
        $ticket->update([
            'tecnico_id' => $nuevoTecnico->tecnico_id,
            'fecha_asignacion' => now(),
            'intentos_escalamiento' => $ticket->intentos_escalamiento + 1
        ]);
        
        $nuevoTecnico->incrementarTicketsAsignados();
        
        // Crear alarma de escalamiento
        app(AlarmaService::class)->crearAlarmaEscalamiento($ticket, $razon);
        
        Log::info("Ticket {$ticket->numero_ticket} reasignado por {$razon}");
        
        return true;
    }
}