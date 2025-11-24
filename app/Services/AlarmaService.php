<?php

namespace App\Services;

use App\Models\Alarma;
use App\Models\Ticket;
use App\Models\Sedes;
use Illuminate\Support\Facades\Log;

class AlarmaService
{
    /**
     * Crear alarma por caída de sistema
     */
    public function crearAlarmaCaidaSistema(Ticket $ticket, Sedes $sede)
    {
        $alarma = Alarma::create([
            'ticket_id' => $ticket->id,
            'tipo_alarma' => 'caida_sistema',
            'nivel' => 'critical',
            'titulo' => "🚨 ALERTA CRÍTICA: Caída de Sistema en {$sede->nombre}",
            'mensaje' => "Se ha detectado una caída de conexión en la sede {$sede->nombre} ({$sede->ciudad}).\n\n" .
                "Ticket: {$ticket->numero_ticket}\n" .
                "IP: {$sede->ip_principal}\n" .
                "Hora: " . now()->format('d/m/Y H:i:s') . "\n" .
                "Técnico asignado: " . ($ticket->tecnico ? $ticket->tecnico->nombre : 'Por asignar') . "\n\n" .
                "Requiere atención inmediata.",
            'canales' => ['email', 'whatsapp'],
            'destinatarios' => $this->obtenerDestinatariosCriticos($ticket),
            'enviada' => false,
            'activa' => true
        ]);

        // Enviar alarma inmediatamente
        $this->enviarAlarma($alarma);

        return $alarma;
    }

    /**
     * Crear alarma por escalamiento
     */
    public function crearAlarmaEscalamiento(Ticket $ticket, $razon)
    {
        $alarma = Alarma::create([
            'ticket_id' => $ticket->id,
            'tipo_alarma' => 'escalamiento',
            'nivel' => 'warning',
            'titulo' => "⚠️ Escalamiento de Ticket: {$ticket->numero_ticket}",
            'mensaje' => "El ticket {$ticket->numero_ticket} ha sido escalado.\n\n" .
                "Razón: {$razon}\n" .
                "Título: {$ticket->titulo}\n" .
                "Prioridad: {$ticket->prioridad->nombre}\n" .
                "Nuevo técnico: {$ticket->tecnico->nombre}\n" .
                "Intentos de escalamiento: {$ticket->intentos_escalamiento}",
            'canales' => ['email'],
            'destinatarios' => $this->obtenerDestinatariosEscalamiento($ticket),
            'enviada' => false,
            'activa' => true
        ]);

        $this->enviarAlarma($alarma);

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
            'titulo' => "🔴 ESCALAMIENTO CRÍTICO: Ticket {$ticket->numero_ticket}",
            'mensaje' => "El ticket {$ticket->numero_ticket} ha sido escalado {$ticket->intentos_escalamiento} veces.\n\n" .
                "REQUIERE ATENCIÓN URGENTE\n\n" .
                "Título: {$ticket->titulo}\n" .
                "Cliente: {$ticket->usuario->nombre}\n" .
                "Prioridad: {$ticket->prioridad->nombre}\n" .
                "Estado: {$ticket->estado}\n" .
                "Tiempo transcurrido: " . $ticket->fecha_apertura->diffForHumans() . "\n" .
                "Técnico actual: {$ticket->tecnico->nombre}",
            'canales' => ['email', 'whatsapp'],
            'destinatarios' => $this->obtenerDestinatariosAdministradores(),
            'enviada' => false,
            'activa' => true
        ]);

        $this->enviarAlarma($alarma);

        return $alarma;
    }

    /**
     * Crear alarma por SLA vencido
     */
    public function crearAlarmaSLAVencido(Ticket $ticket, $tipoSLA)
    {
        $alarma = Alarma::create([
            'ticket_id' => $ticket->id,
            'tipo_alarma' => 'sla_vencido',
            'nivel' => 'warning',
            'titulo' => "⏰ SLA Vencido: Ticket {$ticket->numero_ticket}",
            'mensaje' => "El SLA de {$tipoSLA} ha sido vencido.\n\n" .
                "Ticket: {$ticket->numero_ticket}\n" .
                "Cliente: {$ticket->usuario->nombre}\n" .
                "Técnico: " . ($ticket->tecnico ? $ticket->tecnico->nombre : 'Sin asignar') . "\n" .
                "Fecha límite: " . ($tipoSLA == 'respuesta' ?
                    $ticket->fecha_limite_respuesta->format('d/m/Y H:i') :
                    $ticket->fecha_limite_resolucion->format('d/m/Y H:i')),
            'canales' => ['email'],
            'destinatarios' => $this->obtenerDestinatariosSLA($ticket),
            'enviada' => false,
            'activa' => true
        ]);

        $this->enviarAlarma($alarma);

        return $alarma;
    }

    /**
     * Enviar alarma por todos los canales configurados
     */
    private function enviarAlarma(Alarma $alarma)
    {
        $notificacionService = app(NotificacionService::class);

        foreach ($alarma->canales as $canal) {
            switch ($canal) {
                case 'email':
                    $notificacionService->enviarEmail($alarma);
                    break;
                case 'whatsapp':
                    $notificacionService->enviarWhatsApp($alarma);
                    break;
                case 'sms':
                    $notificacionService->enviarSMS($alarma);
                    break;
            }
        }

        $alarma->marcarComoEnviada();
    }

    /**
     * Obtener destinatarios críticos (admins + técnicos senior)
     */
    private function obtenerDestinatariosCriticos(Ticket $ticket)
    {
        $destinatarios = [];

        // Administradores
        $admins = \App\Models\Usuario::where('rol', 'admin')->where('estado', true)->get();
        foreach ($admins as $admin) {
            $destinatarios[] = [
                'nombre' => $admin->nombre,
                'email' => $admin->email,
                'telefono' => $admin->telefono
            ];
        }

        // Técnico asignado
        if ($ticket->tecnico) {
            $destinatarios[] = [
                'nombre' => $ticket->tecnico->nombre,
                'email' => $ticket->tecnico->email,
                'telefono' => $ticket->tecnico->telefono
            ];
        }

        return $destinatarios;
    }

    /**
     * Obtener destinatarios para escalamiento
     */
    private function obtenerDestinatariosEscalamiento(Ticket $ticket)
    {
        $destinatarios = [];

        // Nuevo técnico asignado
        if ($ticket->tecnico) {
            $destinatarios[] = [
                'nombre' => $ticket->tecnico->nombre,
                'email' => $ticket->tecnico->email,
                'telefono' => $ticket->tecnico->telefono
            ];
        }

        return $destinatarios;
    }

    /**
     * Obtener destinatarios administradores
     */
    private function obtenerDestinatariosAdministradores()
    {
        $destinatarios = [];

        $admins = \App\Models\Usuario::where('rol', 'admin')->where('estado', true)->get();
        foreach ($admins as $admin) {
            $destinatarios[] = [
                'nombre' => $admin->nombre,
                'email' => $admin->email,
                'telefono' => $admin->telefono
            ];
        }

        return $destinatarios;
    }

    /**
     * Obtener destinatarios para alertas de SLA
     */
    private function obtenerDestinatariosSLA(Ticket $ticket)
    {
        $destinatarios = [];

        if ($ticket->tecnico) {
            $destinatarios[] = [
                'nombre' => $ticket->tecnico->nombre,
                'email' => $ticket->tecnico->email,
                'telefono' => $ticket->tecnico->telefono
            ];
        }

        return $destinatarios;
    }
}
