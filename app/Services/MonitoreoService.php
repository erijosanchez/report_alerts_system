<?php

namespace App\Services;

use App\Models\Sede;
use App\Models\MonitoreoLog;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class MonitoreoService
{
    /**
     * Monitorear todas las sedes activas
     */
    public function monitorearSedes()
    {
        $sedes = Sede::where('activa', true)->get();

        foreach ($sedes as $sede) {
            $this->monitorearSede($sede);
        }
    }

    /**
     * Monitorear una sede específica
     */
    public function monitorearSede(Sede $sede)
    {
        Log::info("Monitoreando sede: {$sede->nombre}");

        // 1. Ping a IP principal
        $pingResultado = $this->hacerPing($sede->ip_principal);

        $this->registrarLog($sede, 'ping', $pingResultado);

        // 2. Si falla IP principal, probar backup
        if (!$pingResultado['success'] && $sede->ip_backup) {
            $pingResultado = $this->hacerPing($sede->ip_backup);
            $this->registrarLog($sede, 'ping_backup', $pingResultado);
        }

        // 3. Si está offline, generar ticket automático
        if (!$pingResultado['success']) {
            if ($sede->estaOnline()) {
                // Cambió de online a offline - GENERAR TICKET
                $this->generarTicketCaida($sede, $pingResultado);
                $sede->marcarComoOffline();
            }
        } else {
            // Si estaba offline y ahora está online, cerrar tickets automáticos
            if (!$sede->estaOnline()) {
                $this->cerrarTicketsAutomaticos($sede);
                $sede->marcarComoOnline();
            }
        }

        // 4. Monitorear servicios específicos
        if ($sede->servicios_monitorear) {
            foreach ($sede->servicios_monitorear as $servicio) {
                $this->monitorearServicio($sede, $servicio);
            }
        }
    }

    /**
     * Hacer ping a una IP
     */
    private function hacerPing($ip)
    {
        $start = microtime(true);

        // Ejecutar ping (Linux/Unix)
        $output = [];
        $return_var = 0;
        exec("ping -c 1 -W 2 {$ip}", $output, $return_var);

        $tiempo = round((microtime(true) - $start) * 1000); // ms

        return [
            'success' => $return_var === 0,
            'tiempo_respuesta' => $tiempo,
            'detalles' => implode("\n", $output)
        ];
    }

    /**
     * Monitorear un servicio HTTP
     */
    private function monitorearServicio(Sede $sede, $servicio)
    {
        // Implementar según el tipo de servicio
        // Ejemplo: chequear puerto, URL, etc.
    }

    /**
     * Registrar log de monitoreo
     */
    private function registrarLog(Sede $sede, $tipo, $resultado)
    {
        MonitoreoLog::create([
            'sede_id' => $sede->id,
            'tipo_chequeo' => $tipo,
            'resultado' => $resultado['success'] ? 'success' : 'failed',
            'tiempo_respuesta_ms' => $resultado['tiempo_respuesta'] ?? null,
            'detalles' => $resultado['detalles'] ?? null,
            'fecha_chequeo' => now()
        ]);
    }

    /**
     * Generar ticket automático por caída de sistema
     */
    private function generarTicketCaida(Sede $sede, $resultado)
    {
        // Verificar si ya existe un ticket abierto para esta sede
        $ticketExistente = Ticket::where('sede_id', $sede->id)
            ->whereIn('estado', ['abierto', 'en_proceso'])
            ->where('generado_automaticamente', true)
            ->first();

        if ($ticketExistente) {
            Log::info("Ya existe ticket abierto para sede {$sede->nombre}");
            return;
        }

        // Crear ticket automático
        $ticket = Ticket::create([
            'titulo' => "CAÍDA DE SISTEMA - {$sede->nombre}",
            'descripcion' => "Se ha detectado automáticamente una caída de conexión en la sede {$sede->nombre} ({$sede->ciudad}).\n\n" .
                "IP Principal: {$sede->ip_principal}\n" .
                "Hora detección: " . now()->format('d/m/Y H:i:s') . "\n" .
                "Último tiempo de respuesta: {$resultado['tiempo_respuesta']}ms\n\n" .
                "Este ticket fue generado automáticamente por el sistema de monitoreo.",
            'usuario_id' => 1, // Sistema
            'categoria_id' => 1, // Soporte Técnico
            'prioridad_id' => 4, // Crítica
            'sede_id' => $sede->id,
            'generado_automaticamente' => true,
            'origen_automatico' => 'monitoreo',
            'asignacion_automatica' => true,
            'metadata_automatizacion' => [
                'tipo_falla' => 'caida_conexion',
                'tiempo_deteccion' => now()->toIso8601String(),
                'ip_fallida' => $sede->ip_principal
            ]
        ]);

        // Asignar automáticamente
        app(AsignacionAutomaticaService::class)->asignarTicket($ticket);

        // Crear alarma crítica
        app(AlarmaService::class)->crearAlarmaCaidaSistema($ticket, $sede);

        Log::critical("Ticket automático generado por caída de sede {$sede->nombre}: {$ticket->numero_ticket}");
    }

    /**
     * Cerrar tickets automáticos cuando la sede se recupera
     */
    private function cerrarTicketsAutomaticos(Sede $sede)
    {
        $tickets = Ticket::where('sede_id', $sede->id)
            ->whereIn('estado', ['abierto', 'en_proceso'])
            ->where('generado_automaticamente', true)
            ->get();

        foreach ($tickets as $ticket) {
            $ticket->update([
                'estado' => 'resuelto',
                'fecha_resolucion' => now()
            ]);

            // Agregar comentario automático
            $ticket->comentarios()->create([
                'usuario_id' => 1, // Sistema
                'comentario' => "La sede {$sede->nombre} ha recuperado la conexión. Ticket cerrado automáticamente.",
                'es_interno' => false
            ]);

            Log::info("Ticket {$ticket->numero_ticket} cerrado automáticamente - Sede recuperada");
        }
    }
}
