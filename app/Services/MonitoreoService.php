<?php

namespace App\Services;

use App\Models\Sede;
use App\Models\MonitoreoLog;
use App\Models\Ticket;
use App\Models\Prioridad;
use App\Models\Categoria;
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
            // Verificar si ya es momento de monitorear
            if ($this->debeMonitorear($sede)) {
                $this->monitorearSede($sede);
            }
        }
    }

    /**
     * Verificar si debe monitorear la sede
     */
    private function debeMonitorear(Sede $sede)
    {
        // Si nunca se ha monitoreado, hacerlo ahora
        if (!$sede->ultima_comprobacion) {
            return true;
        }

        // Verificar si ya pasó el intervalo configurado
        $minutosDesdeUltima = now()->diffInMinutes($sede->ultima_comprobacion);
        
        return $minutosDesdeUltima >= ($sede->intervalo_monitoreo ?? 5);
    }

    /**
     * Monitorear una sede específica
     */
    public function monitorearSede(Sede $sede)
    {
        $logData = [
            'sede_id' => $sede->id,
            'tipo_chequeo' => 'ping',
            'fecha_chequeo' => now()
        ];

        try {
            // Guardar estado anterior
            $estadoAnterior = $sede->estado_conexion;
            $logData['estado_anterior'] = $estadoAnterior;

            // 1. Hacer ping
            $pingResultado = $this->hacerPing($sede->ip_principal);
            
            $logData['resultado'] = $pingResultado['success'] ? 'success' : 'fail';
            $logData['latencia_ms'] = $pingResultado['tiempo_respuesta'] ?? null;
            $logData['detalles'] = $pingResultado['detalles'];

            // 2. Si falla IP principal, probar backup
            if (!$pingResultado['success'] && $sede->ip_backup) {
                $pingResultado = $this->hacerPing($sede->ip_backup);
                $logData['tipo_chequeo'] = 'ping_backup';
                $logData['resultado'] = $pingResultado['success'] ? 'success' : 'fail';
                $logData['latencia_ms'] = $pingResultado['tiempo_respuesta'] ?? null;
                $logData['detalles'] .= " | Backup: " . $pingResultado['detalles'];
            }

            // 3. Determinar nuevo estado
            $nuevoEstado = $this->determinarEstado($pingResultado);
            $logData['estado_nuevo'] = $nuevoEstado;

            // 4. Actualizar sede
            $sede->update([
                'estado_conexion' => $nuevoEstado,
                'ultima_comprobacion' => now()
            ]);

            // 5. Verificar si cambió de estado
            $cambioEstado = ($estadoAnterior !== $nuevoEstado);
            $logData['cambio_estado'] = $cambioEstado;

            // 6. Si cambió de online/degradado a offline - GENERAR TICKET
            if (in_array($estadoAnterior, ['online', 'degradado', 'pendiente']) && $nuevoEstado === 'offline') {
                $ticketGenerado = $this->generarTicketCaida($sede, $pingResultado);
                $logData['ticket_generado'] = $ticketGenerado ? 'SI' : 'NO';
                $logData['detalles'] .= " | Generación ticket: " . ($ticketGenerado ? 'EXITOSA' : 'OMITIDA - Ya existe ticket abierto');
            }

            // 7. Si cambió de offline a online - CERRAR TICKETS
            if ($estadoAnterior === 'offline' && in_array($nuevoEstado, ['online', 'degradado'])) {
                $ticketsCerrados = $this->cerrarTicketsAutomaticos($sede);
                $logData['tickets_cerrados'] = $ticketsCerrados;
                $logData['detalles'] .= " | Tickets cerrados: {$ticketsCerrados}";
            }

            // 8. Guardar log
            MonitoreoLog::create($logData);

        } catch (\Exception $e) {
            // Guardar error en log
            $logData['resultado'] = 'error';
            $logData['detalles'] = "ERROR: " . $e->getMessage();
            MonitoreoLog::create($logData);
            
            Log::error("Error monitoreando sede {$sede->nombre}: " . $e->getMessage());
        }
    }

    /**
     * Hacer ping a una IP
     */
    private function hacerPing($ip)
    {
        $start = microtime(true);

        // Ejecutar ping según sistema operativo
        $output = [];
        $return_var = 0;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: -n = número de paquetes, -w = timeout en ms
            exec("ping -n 1 -w 2000 {$ip}", $output, $return_var);
        } else {
            // Linux/Unix: -c = count, -W = timeout en segundos
            exec("ping -c 1 -W 2 {$ip}", $output, $return_var);
        }

        $tiempo = round((microtime(true) - $start) * 1000);

        // Crear mensaje simple sin caracteres especiales
        if ($return_var === 0) {
            $detalles = sprintf(
                "Ping exitoso | IP: %s | Latencia: %dms | OS: %s",
                $ip,
                $tiempo,
                PHP_OS
            );
        } else {
            $detalles = sprintf(
                "Ping fallido | IP: %s | Host no alcanzable | OS: %s | Return code: %d",
                $ip,
                PHP_OS,
                $return_var
            );
        }

        return [
            'success' => $return_var === 0,
            'tiempo_respuesta' => $tiempo,
            'detalles' => $detalles
        ];
    }

    /**
     * Determinar estado según resultado del ping
     */
    private function determinarEstado($resultado)
    {
        if (!$resultado['success']) {
            return 'offline';
        }

        // Si la latencia es muy alta (>500ms), marcar como degradado
        if ($resultado['tiempo_respuesta'] > 500) {
            return 'degradado';
        }

        return 'online';
    }

    /**
     * Generar ticket automático por caída de sistema
     * @return bool true si se generó ticket, false si ya existía
     */
    private function generarTicketCaida(Sede $sede, $resultado)
    {
        // Verificar si ya existe un ticket abierto para esta sede
        $ticketExistente = Ticket::where('sede_id', $sede->id)
            ->whereIn('estado', ['abierto', 'en_proceso'])
            ->where('origen', 'automatico')
            ->first();

        if ($ticketExistente) {
            return false; // Ya existe ticket
        }

        try {
            // Buscar prioridad y categoría
            $prioridad = Prioridad::where('nombre', 'ILIKE', '%alta%')
                ->orWhere('nombre', 'ILIKE', '%urgente%')
                ->orWhere('nombre', 'ILIKE', '%crítica%')
                ->first();
            
            if (!$prioridad) {
                $prioridad = Prioridad::first(); // Tomar la primera que exista
            }

            $categoria = Categoria::where('nombre', 'ILIKE', '%infraestructura%')
                ->orWhere('nombre', 'ILIKE', '%soporte%')
                ->orWhere('nombre', 'ILIKE', '%red%')
                ->first();

            if (!$categoria) {
                $categoria = Categoria::first(); // Tomar la primera que exista
            }

            // Usuario sistema
            $usuarioSistema = \App\Models\Usuario::where('rol', 'admin')->first();
            
            if (!$usuarioSistema) {
                $usuarioSistema = \App\Models\Usuario::first(); // Tomar el primer usuario
            }

            if (!$usuarioSistema || !$prioridad) {
                Log::error("No se puede crear ticket: faltan usuario o prioridad");
                return false;
            }

            // Crear ticket automático
            $ticket = Ticket::create([
                'numero_ticket' => $this->generarNumeroTicket(),
                'titulo' => "🚨 CAÍDA DE SISTEMA - {$sede->nombre}",
                'descripcion' => "Se ha detectado automáticamente una caída de conexión en la sede {$sede->nombre} ({$sede->ciudad}).\n\n" .
                    "📍 Código Sede: {$sede->codigo}\n" .
                    "📍 IP Principal: {$sede->ip_principal}\n" .
                    "⏰ Hora detección: " . now()->format('d/m/Y H:i:s') . "\n" .
                    "📊 Tiempo de respuesta: {$resultado['tiempo_respuesta']}ms\n" .
                    "📋 Detalles técnicos: {$resultado['detalles']}\n\n" .
                    "🤖 Este ticket fue generado automáticamente por el sistema de monitoreo.\n" .
                    "⚠️ Requiere atención inmediata.",
                'usuario_id' => $usuarioSistema->id,
                'categoria_id' => $categoria->id ?? null,
                'prioridad_id' => $prioridad->id,
                'sede_id' => $sede->id,
                'estado' => 'abierto',
                'origen' => 'automatico',
                'fecha_apertura' => now()
            ]);

            Log::info("✅ Ticket automático creado: {$ticket->numero_ticket} para sede {$sede->nombre}");

            // Intentar asignar automáticamente
            try {
                app(AsignacionAutomaticaService::class)->asignarTicket($ticket);
            } catch (\Exception $e) {
                Log::warning("No se pudo asignar ticket automáticamente: " . $e->getMessage());
            }

            // Intentar crear alarma
            try {
                app(AlarmaService::class)->crearAlarmaCaidaSistema($ticket, $sede);
            } catch (\Exception $e) {
                Log::warning("No se pudo crear alarma: " . $e->getMessage());
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error generando ticket para sede {$sede->nombre}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cerrar tickets automáticos cuando la sede se recupera
     * @return int Cantidad de tickets cerrados
     */
    private function cerrarTicketsAutomaticos(Sede $sede)
    {
        $tickets = Ticket::where('sede_id', $sede->id)
            ->whereIn('estado', ['abierto', 'en_proceso'])
            ->where('origen', 'automatico')
            ->get();

        $contador = 0;

        foreach ($tickets as $ticket) {
            try {
                $ticket->update([
                    'estado' => 'resuelto',
                    'fecha_cierre' => now(),
                    'tiempo_resolucion_minutos' => round($ticket->fecha_apertura->diffInMinutes(now()))
                ]);

                // Agregar comentario automático
                \App\Models\Comentario::create([
                    'ticket_id' => $ticket->id,
                    'usuario_id' => $ticket->usuario_id,
                    'comentario' => "✅ La sede {$sede->nombre} ha recuperado la conexión.\n\nEstado actual: {$sede->estado_conexion}\nFecha de recuperación: " . now()->format('d/m/Y H:i:s') . "\n\n🤖 Ticket cerrado automáticamente por el sistema de monitoreo.",
                    'es_interno' => false
                ]);

                Log::info("Ticket {$ticket->numero_ticket} cerrado automáticamente - Sede {$sede->nombre} recuperada");
                $contador++;

            } catch (\Exception $e) {
                Log::error("Error cerrando ticket {$ticket->numero_ticket}: " . $e->getMessage());
            }
        }

        return $contador;
    }

    /**
     * Generar número de ticket único
     */
    private function generarNumeroTicket()
    {
        $year = now()->year;
        $ultimo = Ticket::whereYear('fecha_apertura', $year)->max('id') ?? 0;
        $consecutivo = str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);
        
        return "TKT-{$year}-{$consecutivo}";
    }
}