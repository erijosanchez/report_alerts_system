<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Sede;
use App\Models\Alarma;
use App\Models\Usuario;
use App\Models\MonitoreoLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $tipoReporte = $request->get('tipo', 'general');
        $desde = $request->get('desde', now()->subMonth()->format('Y-m-d'));
        $hasta = $request->get('hasta', now()->format('Y-m-d'));

        $data = match ($tipoReporte) {
            'tickets' => $this->reporteTickets($desde, $hasta),
            'monitoreo' => $this->reporteMonitoreo($desde, $hasta),
            'alarmas' => $this->reporteAlarmas($desde, $hasta),
            'tecnicos' => $this->reporteTecnicos($desde, $hasta),
            'sla' => $this->reporteSLA($desde, $hasta),
            default => $this->reporteGeneral($desde, $hasta)
        };

        return view('reportes.index', compact('tipoReporte', 'desde', 'hasta', 'data'));
    }

    private function reporteGeneral($desde, $hasta)
    {
        return [
            'tickets' => [
                'total' => Ticket::whereBetween('fecha_apertura', [$desde, $hasta])->count(),
                'abiertos' => Ticket::where('estado', 'abierto')->count(),
                'en_proceso' => Ticket::where('estado', 'en_proceso')->count(),
                'resueltos' => Ticket::where('estado', 'resuelto')
                    ->whereBetween('fecha_cierre', [$desde, $hasta])->count(),
                'cerrados' => Ticket::where('estado', 'cerrado')
                    ->whereBetween('fecha_cierre', [$desde, $hasta])->count(),
            ],
            'sedes' => [
                'total' => Sede::count(),
                'online' => Sede::where('estado_conexion', 'online')->count(),
                'offline' => Sede::where('estado_conexion', 'offline')->count(),
                'degradado' => Sede::where('estado_conexion', 'degradado')->count(),
            ],
            'alarmas' => [
                'total' => Alarma::whereBetween('created_at', [$desde, $hasta])->count(),
                'criticas' => Alarma::where('nivel', 'critical')
                    ->where('activa', true)->count(),
                'advertencias' => Alarma::where('nivel', 'warning')
                    ->where('activa', true)->count(),
            ],
            'tiempos' => [
                'respuesta_promedio' => $this->calcularTiempoPromedioRespuesta($desde, $hasta),
                'resolucion_promedio' => $this->calcularTiempoPromedioResolucion($desde, $hasta),
            ],
            'sla' => [
                'cumplimiento' => $this->calcularPorcentajeSLACumplido($desde, $hasta),
                'tickets_vencidos' => $this->contarTicketsVencidosSLA($desde, $hasta),
            ]
        ];
    }

    private function reporteTickets($desde, $hasta)
    {
        $tickets = Ticket::whereBetween('fecha_apertura', [$desde, $hasta])
            ->with(['categoria', 'prioridad', 'tecnico', 'usuario'])
            ->get();

        return [
            'tickets' => $tickets,
            'total' => $tickets->count(),
            'por_estado' => $tickets->groupBy('estado')->map->count(),
            'por_categoria' => $tickets->groupBy('categoria.nombre')->map->count(),
            'por_prioridad' => $tickets->groupBy('prioridad.nombre')->map->count(),
            'por_origen' => $tickets->groupBy('origen')->map->count(),
            'automaticos' => $tickets->where('origen', 'automatico')->count(),
            'manuales' => $tickets->where('origen', 'manual')->count(),
            'tiempo_respuesta_promedio' => $this->calcularTiempoPromedioRespuesta($desde, $hasta),
            'tiempo_resolucion_promedio' => $this->calcularTiempoPromedioResolucion($desde, $hasta),
        ];
    }

    private function reporteMonitoreo($desde, $hasta)
    {
        $sedes = Sede::with(['monitoreoLogs' => function ($query) use ($desde, $hasta) {
            $query->whereBetween('fecha_chequeo', [$desde, $hasta]);
        }])->get();

        $estadisticas = [];
        foreach ($sedes as $sede) {
            $logs = $sede->monitoreoLogs;
            $totalChecks = $logs->count();
            $successChecks = $logs->where('resultado', 'success')->count();

            $estadisticas[] = [
                'sede' => $sede,
                'uptime' => $totalChecks > 0 ? round(($successChecks / $totalChecks) * 100, 2) : 0,
                'latencia_promedio' => $logs->where('resultado', 'success')->avg('latencia_ms') ?? 0,
                'total_checks' => $totalChecks,
                'checks_exitosos' => $successChecks,
                'checks_fallidos' => $logs->where('resultado', 'fail')->count(),
                'incidentes' => $logs->where('resultado', 'fail')->count(),
            ];
        }

        return [
            'estadisticas' => collect($estadisticas)->sortByDesc('incidentes'),
            'uptime_general' => collect($estadisticas)->avg('uptime'),
        ];
    }

    private function reporteAlarmas($desde, $hasta)
    {
        $alarmas = Alarma::whereBetween('created_at', [$desde, $hasta])
            ->with(['ticket.sede'])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'alarmas' => $alarmas,
            'total' => $alarmas->count(),
            'por_nivel' => $alarmas->groupBy('nivel')->map->count(),
            'por_tipo' => $alarmas->groupBy('tipo_alarma')->map->count(),
            'activas' => $alarmas->where('activa', true)->count(),
            'resueltas' => $alarmas->where('activa', false)->count(),
            'tiempo_promedio_resolucion' => $this->calcularTiempoPromedioResolucionAlarmas($alarmas),
        ];
    }

    private function reporteTecnicos($desde, $hasta)
    {
        $tecnicos = Usuario::where('rol', 'tecnico')
            ->where('estado', true)
            ->with(['ticketsAsignados' => function ($query) use ($desde, $hasta) {
                $query->whereBetween('fecha_apertura', [$desde, $hasta]);
            }])
            ->get();

        $estadisticas = [];
        foreach ($tecnicos as $tecnico) {
            $tickets = $tecnico->ticketsAsignados;
            $ticketsResueltos = $tickets->where('estado', 'resuelto');

            $estadisticas[] = [
                'tecnico' => $tecnico,
                'tickets_asignados' => $tickets->count(),
                'tickets_resueltos' => $ticketsResueltos->count(),
                'tickets_pendientes' => $tickets->whereIn('estado', ['abierto', 'en_proceso'])->count(),
                'tiempo_promedio_resolucion' => $ticketsResueltos->avg('tiempo_resolucion_minutos') ?? 0,
                'tasa_resolucion' => $tickets->count() > 0
                    ? round(($ticketsResueltos->count() / $tickets->count()) * 100, 2)
                    : 0,
                'sla_cumplimiento' => $this->calcularSLATecnico($tecnico->id, $desde, $hasta),
            ];
        }

        return [
            'estadisticas' => collect($estadisticas)->sortByDesc('tickets_asignados'),
        ];
    }

    private function reporteSLA($desde, $hasta)
    {
        $tickets = Ticket::whereBetween('fecha_apertura', [$desde, $hasta])
            ->with(['prioridad', 'tecnico'])
            ->get();

        $ticketsCerrados = $tickets->whereIn('estado', ['resuelto', 'cerrado']);
        $ticketsConSLA = $ticketsCerrados->filter(function ($ticket) {
            return $ticket->sla_vencimiento != null;
        });

        $ticketsCumplidosSLA = $ticketsConSLA->filter(function ($ticket) {
            return $ticket->fecha_cierre && $ticket->fecha_cierre <= $ticket->sla_vencimiento;
        });

        $ticketsVencidosSLA = $ticketsConSLA->filter(function ($ticket) {
            return $ticket->fecha_cierre && $ticket->fecha_cierre > $ticket->sla_vencimiento;
        });

        $porPrioridad = [];
        foreach ($tickets->groupBy('prioridad.nombre') as $prioridad => $ticketsPrioridad) {
            $conSLA = $ticketsPrioridad->filter(fn($t) => $t->sla_vencimiento != null);
            $cumplidos = $conSLA->filter(fn($t) => $t->fecha_cierre && $t->fecha_cierre <= $t->sla_vencimiento);

            $porPrioridad[$prioridad] = [
                'total' => $conSLA->count(),
                'cumplidos' => $cumplidos->count(),
                'vencidos' => $conSLA->count() - $cumplidos->count(),
                'porcentaje' => $conSLA->count() > 0
                    ? round(($cumplidos->count() / $conSLA->count()) * 100, 2)
                    : 0
            ];
        }

        return [
            'total_tickets' => $ticketsConSLA->count(),
            'tickets_cumplidos' => $ticketsCumplidosSLA->count(),
            'tickets_vencidos' => $ticketsVencidosSLA->count(),
            'porcentaje_cumplimiento' => $ticketsConSLA->count() > 0
                ? round(($ticketsCumplidosSLA->count() / $ticketsConSLA->count()) * 100, 2)
                : 100,
            'por_prioridad' => $porPrioridad,
            'tickets_vencidos_detalle' => $ticketsVencidosSLA,
        ];
    }

    // Métodos auxiliares
    private function calcularTiempoPromedioRespuesta($desde, $hasta)
    {
        $promedio = Ticket::whereBetween('fecha_apertura', [$desde, $hasta])
            ->whereNotNull('tiempo_respuesta_minutos')
            ->avg('tiempo_respuesta_minutos');

        return round($promedio ?? 0);
    }

    private function calcularTiempoPromedioResolucion($desde, $hasta)
    {
        $promedio = Ticket::whereBetween('fecha_apertura', [$desde, $hasta])
            ->whereIn('estado', ['resuelto', 'cerrado'])
            ->whereNotNull('tiempo_resolucion_minutos')
            ->avg('tiempo_resolucion_minutos');

        return round($promedio ?? 0);
    }

    private function calcularPorcentajeSLACumplido($desde, $hasta)
    {
        $tickets = Ticket::whereBetween('fecha_apertura', [$desde, $hasta])
            ->whereIn('estado', ['resuelto', 'cerrado'])
            ->whereNotNull('sla_vencimiento')
            ->get();

        if ($tickets->isEmpty()) return 100;

        $cumplidos = $tickets->filter(function ($ticket) {
            return $ticket->fecha_cierre && $ticket->fecha_cierre <= $ticket->sla_vencimiento;
        })->count();

        return round(($cumplidos / $tickets->count()) * 100, 2);
    }

    private function contarTicketsVencidosSLA($desde, $hasta)
    {
        return Ticket::whereBetween('fecha_apertura', [$desde, $hasta])
            ->whereIn('estado', ['resuelto', 'cerrado'])
            ->whereNotNull('sla_vencimiento')
            ->whereNotNull('fecha_cierre')
            ->whereColumn('fecha_cierre', '>', 'sla_vencimiento')
            ->count();
    }

    private function calcularSLATecnico($tecnicoId, $desde, $hasta)
    {
        $tickets = Ticket::where('tecnico_id', $tecnicoId)
            ->whereBetween('fecha_apertura', [$desde, $hasta])
            ->whereIn('estado', ['resuelto', 'cerrado'])
            ->whereNotNull('sla_vencimiento')
            ->get();

        if ($tickets->isEmpty()) return 100;

        $cumplidos = $tickets->filter(function ($ticket) {
            return $ticket->fecha_cierre && $ticket->fecha_cierre <= $ticket->sla_vencimiento;
        })->count();

        return round(($cumplidos / $tickets->count()) * 100, 2);
    }

    private function calcularTiempoPromedioResolucionAlarmas($alarmas)
    {
        $alarmasResueltas = $alarmas->where('activa', false);

        if ($alarmasResueltas->isEmpty()) return 0;

        $tiempoTotal = 0;
        foreach ($alarmasResueltas as $alarma) {
            $tiempoTotal += $alarma->created_at->diffInMinutes($alarma->updated_at);
        }

        return round($tiempoTotal / $alarmasResueltas->count());
    }
}
