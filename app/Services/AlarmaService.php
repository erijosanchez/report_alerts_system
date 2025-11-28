<?php

namespace App\Services;

use App\Models\Alarma;
use App\Models\Ticket;
use App\Models\Sede;
use Illuminate\Support\Facades\Log;

class AlarmaService
{

    private function generarTitulo($tipo, $nivel, Ticket $ticket = null, Sede $sede = null)
    {
        switch ($tipo) {

            case 'conexion_perdida':
                return $sede
                    ? "🚨 Caída de Sistema en {$sede->nombre}"
                    : "🚨 Caída de Sistema";

            case 'escalamiento':
                if ($nivel === 'critical') {
                    return $ticket
                        ? "🔴 Escalamiento CRÍTICO del Ticket {$ticket->numero_ticket}"
                        : "🔴 Escalamiento CRÍTICO";
                }
                return $ticket
                    ? "⚠️ Ticket Escalado: {$ticket->numero_ticket}"
                    : "⚠️ Ticket Escalado";

            default:
                return "🔔 Notificación del Sistema";
        }
    }


    /**
     * Crear alarma por caída de sistema
     */
    public function crearAlarmaCaidaSistema(Ticket $ticket, Sede $sede)
    {
        $alarma = Alarma::create([
            'ticket_id' => $ticket->id,
            'tipo_alarma' => 'conexion_perdida',
            'nivel' => 'critical',
            'titulo'  => $this->generarTitulo('conexion_perdida', 'critical', $ticket, $sede),
            'mensaje' => "🚨 ALERTA CRÍTICA: Caída de Sistema en {$sede->nombre}",
            'detalle' => "Se ha detectado una caída de conexión en la sede {$sede->nombre} ({$sede->ciudad}).\n\n" .
                "📋 Ticket: {$ticket->numero_ticket}\n" .
                "📍 IP: {$sede->ip_principal}\n" .
                "⏰ Hora: " . now()->format('d/m/Y H:i:s') . "\n" .
                "👤 Técnico asignado: " . ($ticket->tecnico ? $ticket->tecnico->nombre : 'Por asignar') . "\n\n" .
                "⚠️ Requiere atención inmediata.",
            'activa' => true,
            'enviada' => false
        ]);

        // Enviar alarma inmediatamente
        $this->enviarAlarma($alarma, $ticket, 'critical');

        return $alarma;
    }
    
    /**
     * Crear alarma por escalamiento - con niveles dinámicos
     */
    public function crearAlarmaEscalamiento(Ticket $ticket, $razon)
    {
        // Determinar nivel según intentos de escalamiento
        $nivel = match ($ticket->intentos_escalamiento) {
            1 => 'warning',      // Primer escalamiento
            2 => 'critical',     // Segundo escalamiento
            default => 'critical' // Tercero o más
        };

        $emoji = $nivel === 'critical' ? '🚨' : '⚠️';

        $alarma = Alarma::create([
            'ticket_id' => $ticket->id,
            'tipo_alarma' => 'escalamiento',
            'nivel' => $nivel,
            'mensaje' => "{$emoji} Escalamiento #{$ticket->intentos_escalamiento}: {$ticket->numero_ticket}",
            'detalle' => "El ticket {$ticket->numero_ticket} ha sido escalado.\n\n" .
                "📋 Razón: {$razon}\n" .
                "📝 Título: {$ticket->titulo}\n" .
                "⚡ Prioridad: {$ticket->prioridad->nombre}\n" .
                "👤 Técnico actual: " . ($ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar') . "\n" .
                "🔄 Escalamientos: {$ticket->intentos_escalamiento}\n" .
                "⏰ Tiempo abierto: " . $ticket->fecha_apertura->diffForHumans(),
            'activa' => true,
            'enviada' => false
        ]);

        // Enviar según nivel
        if ($nivel === 'critical' || $ticket->intentos_escalamiento >= 2) {
            // Crítico: Email + WhatsApp a admins y técnico
            $this->enviarAlarma($alarma, $ticket, 'critical');
        } else {
            // Primer escalamiento: Solo email y WhatsApp al nuevo técnico
            $this->enviarAlarma($alarma, $ticket, 'warning_plus');
        }

        return $alarma;
    }

    /**
     * Crear alarma por escalamiento crítico (múltiples escalamientos)
     */
    public function crearAlarmaEscalamientoCritico(Ticket $ticket)
    {
        $alarma = Alarma::create([
            'ticket_id' => $ticket->id,
            'tipo_alarma' => 'escalamiento',
            'nivel' => 'critical',
            'titulo' => $this->generarTitulo('escalamiento', 'critical', $ticket),
            'mensaje' => "🔴 ESCALAMIENTO CRÍTICO: Ticket {$ticket->numero_ticket}",
            'detalle' => "El ticket {$ticket->numero_ticket} ha sido escalado {$ticket->intentos_escalamiento} veces.\n\n" .
                "🚨 REQUIERE ATENCIÓN URGENTE\n\n" .
                "📝 Título: {$ticket->titulo}\n" .
                "👤 Cliente: {$ticket->usuario->nombre}\n" .
                "⚡ Prioridad: {$ticket->prioridad->nombre}\n" .
                "📊 Estado: {$ticket->estado}\n" .
                "⏰ Tiempo transcurrido: " . $ticket->fecha_apertura->diffForHumans() . "\n" .
                "👨‍💻 Técnico actual: " . ($ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar'),
            'activa' => true,
            'enviada' => false
        ]);

        $this->enviarAlarma($alarma, $ticket, 'critical');

        return $alarma;
    }

    /**
     * Enviar alarma por los canales apropiados según nivel
     */
    private function enviarAlarma(Alarma $alarma, Ticket $ticket, $nivel)
    {
        $notificacionService = app(NotificacionService::class);

        // Determinar destinatarios según nivel
        $destinatarios = $this->obtenerDestinatarios($ticket, $nivel);

        // Según el nivel, enviar por diferentes canales
        switch ($nivel) {
            case 'critical':
                // Crítico: Email + WhatsApp + SMS
                $notificacionService->enviarEmail($alarma, $destinatarios);
                $notificacionService->enviarWhatsApp($alarma, $destinatarios);
                break;

            case 'warning':
                // Advertencia: Solo Email
                $notificacionService->enviarEmail($alarma, $destinatarios);
                break;

            case 'info':
                // Informativo: Solo Email
                $notificacionService->enviarEmail($alarma, $destinatarios);
                break;
        }

        $alarma->update(['enviada' => true]);
    }

    /**
     * Obtener destinatarios según nivel de criticidad
     */
    private function obtenerDestinatarios(Ticket $ticket, $nivel)
    {
        $destinatarios = [];

        if ($nivel === 'critical') {
            // Crítico: Admins + Técnico asignado
            $admins = \App\Models\Usuario::where('rol', 'admin')->where('estado', true)->get();
            foreach ($admins as $admin) {
                $destinatarios[] = [
                    'nombre' => $admin->nombre . ' ' . $admin->apellido,
                    'email' => $admin->email,
                    'telefono' => $admin->telefono
                ];
            }
        }

        // Siempre incluir técnico asignado
        if ($ticket->tecnico) {
            $destinatarios[] = [
                'nombre' => $ticket->tecnico->nombre . ' ' . $ticket->tecnico->apellido,
                'email' => $ticket->tecnico->email,
                'telefono' => $ticket->tecnico->telefono
            ];
        }

        return $destinatarios;
    }
}
